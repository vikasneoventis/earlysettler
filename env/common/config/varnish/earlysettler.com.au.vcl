vcl 4.0;
backend default {
  .host = "${NGINX_PORT_80_TCP_ADDR}";
  .port = "8080";
  .connect_timeout = 18000s;
  .first_byte_timeout = 18000s;
  .between_bytes_timeout = 18000s;
}

sub vcl_recv {
    #uncomment this to affectively disable varnish
    #return(pass);

    # Allow Blackfire queries through varnish
    if (req.http.X-Blackfire-Query) {
        return (pass);
    }

    # Always pass for ELB healthcheck
    if (req.url ~ "healthcheck.php") {
        return(pass);
    }

    # Always pass for cloudfront requests as cloudfront is affectively the cache in this case
    if (req.http.user-agent ~ "Amazon CloudFront") {
        return (pass);
    }

    if (req.restarts == 0) {
        if (req.http.x-forwarded-for) {
            set req.http.X-Client-Ip = req.http.x-forwarded-for;
            set req.http.X-Forwarded-For = req.http.X-Forwarded-For + ", " + client.ip;
        } else {
        set req.http.X-Client-Ip = client.ip;
            set req.http.X-Forwarded-For = client.ip;
        }
    }


    # purge request
    if (req.method == "PURGE") {
        ban("obj.http.X-Purge-Host ~ " + req.http.X-Purge-Host + " && obj.http.X-Purge-URL ~ " + req.http.X-Purge-Regex + " && obj.http.Content-Type ~ " + req.http.X-Purge-Content-Type);
        return(synth(200, "Purged."));
    }

    # purge from frontend
    if (req.url ~ "varnishrefresh=1") {
        set req.url = regsuball(req.url,"([&]|[?]?)varnishrefresh=1","");

        if(req.url ~"^\/$") {
            set req.url = "/home";
        }

        if(req.url ~ "[?]" || req.url == "/" || req.url == "/home") {
           #ban("obj.http.X-Purge-Host ~ " + req.http.host + " && obj.http.X-Purge-URL == " + req.url);
            ban("obj.http.X-Purge-URL == " + req.url);
        } else {
            ban("obj.http.X-Purge-Host ~ " + req.http.host + " && obj.http.X-Purge-URL ~ " + req.url);
        }

        return(synth(200, "Purged."));
    }

    #whitelist balance ips, ACLs would be better here but varnish only supports ACL on client.ip which in this case is the load balancer
    #if (
    #    !(req.http.X-Forwarded-For ~ "123.243.49.139")
    #    && !(req.http.X-Forwarded-For ~ "115.70.205.114")
    #    && !(req.http.X-Forwarded-For ~ "59.167.140.37")
    #    && !(req.http.X-Forwarded-For ~ "113.190.40.8")
    # ) {
    #    return(synth(403, "Access Denied"));
    # }

    if(req.http.user-agent ~ "(Google|bing)bot" && req.url ~ "^/(ajax)") {
        return(synth(250, "Bots."));
    }

    if (req.method != "GET" &&
      req.method != "HEAD" &&
      req.method != "PUT" &&
      req.method != "POST" &&
      req.method != "TRACE" &&
      req.method != "OPTIONS" &&
      req.method != "DELETE" &&
      req.method != "PURGE") {
        /* Non-RFC2616 or CONNECT which is weird. */
        return (pipe);
    }

    # we only deal with GET and HEAD by default
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    # don't cache following url pattern
    if(req.url ~ "memcached" || req.url ~"^/(feeds)" || req.url ~ "^/(directory)" || req.http.Content-Type ~ "soap" ||  req.url ~ "xhprof_html" || req.url ~ "_profile=" || req.url ~ "^/(gift[\-]?card)" || req.url ~ "^/(newsletter)" || req.url ~ "^/(innoproductquestions)" || req.url ~ "^/(blog)" || req.url ~ "^/(eway)" ||  req.url ~ "^/(login)" ||  req.url ~ "/(giftcard)" || req.url ~ "lcompare" || req.url ~ "product_compare" || req.url ~ "^/(apcc.php)" || req.url ~ "^/(apc.php)" || req.url ~ "^/(bi_ajax)" || req.url ~ "^/(onestepcheckout)" || req.url ~ "^/(ajaxcart)" || req.url ~ "^/(ajax)" || req.url ~ "^/(checkout)" || (req.url ~ "^/(customer)") || req.url ~ "^/(wishlist)" || req.url ~ "^/(paypal)" || req.url ~ "^/(api)" || req.url ~ "^/(sales)" || req.url ~ "^/(facebookall)" || req.url ~ "healthcheck.php") {
        return (pass);
    }

     # do not cache any page has url param no_cache
    if (req.url ~ "NO_CACHE") {
    	return (pass);
    }

    # do not cache AW ajax extension
    if (req.url ~ "awafptc") {
        return (pass);
    }

    #for adminhtml
    if (req.url ~ "admin") {
        return (pass);
    }

    # not cacheable by defaul
    if (req.http.Authorization || req.url ~ "^https" || req.http.Front-End-Https || req.http.X-Forwarded-Proto ~ "(?i)https") {
        return (pass);
    }

    # unset cookie
    #if (req.http.cookie) {
    #	unset req.http.cookie;
    #}

    #if (req.http.cookie) {
    #  set req.http.cookie = ";" + req.http.cookie;
    #  set req.http.cookie = regsuball(req.http.cookie, "; +", ";");
    #  set req.http.cookie = regsuball(req.http.cookie, ";(frontend|adminhtml|EXTERNAL_NO_CACHE)=", "; \1=");
    #  set req.http.cookie = regsuball(req.http.cookie, ";[^ ][^;]*", "");
    #  set req.http.cookie = regsuball(req.http.cookie, "^[; ]+|[; ]+$", "");

    #  if (req.http.cookie == "") {
    #     unset req.http.cookie;
    #  }
    #}

    # normalize url in case of leading HTTP scheme and domain
    set req.url = regsub(req.url, "^http[s]?://[^/]+", "");

    # static files are always cacheable. unset SSL flag and cookie
    if (req.url ~ "^/(media|js|skin)/.*\.(png|jpg|jpeg|gif|swf|ico)$") {
        unset req.http.Https;
        unset req.http.Cookie;
    }

    # as soon as we have a NO_CACHE cookie pass request (useless)?
    if (req.http.cookie ~ "NO_CACHE=") {
        return (pass);
    }

    # do not cache any page from (useless)?
    # - index files
    # - ...
    if (req.url ~ "^/(index)") {
        return (pass);
    }

    # normalize Accept-Encoding header
    # http://varnish.projects.linpro.no/wiki/FAQ/Compression
    if (req.http.Accept-Encoding) {
        if (req.url ~ "\.(jpg|png|gif|gz|tgz|bz2|tbz|mp3|ogg|swf|flv)$") {
            # No point in compressing these
            unset req.http.Accept-Encoding;
        } elsif (req.http.Accept-Encoding ~ "gzip") {
            set req.http.Accept-Encoding = "gzip";
        } elsif (req.http.Accept-Encoding ~ "deflate" && req.http.user-agent !~ "MSIE") {
            set req.http.Accept-Encoding = "deflate";
        } else {
            # unkown algorithm
            unset req.http.Accept-Encoding;
        }
    }

    # unset Google gclid parameters
    set req.url = regsuball(req.url,"\?gclid=[^&]+$",""); # strips when QS = "?gclid=AAA"
    set req.url = regsuball(req.url,"\?gclid=[^&]+&","?"); # strips when QS = "?gclid=AAA&foo=bar"
    set req.url = regsuball(req.url,"&gclid=[^&]+",""); # strips when QS = "?foo=bar&gclid=AAA" or QS = "?foo=bar&gclid=AAA&bar=baz"

    set req.url = regsuball(req.url,"&((utm)|(mc))_[^=]+=[^&]+",""); # strips when QS = "?foo=bar&utm_xxxx=AAA" or QS = "?foo=bar&utm_xxxx=AAA&bar=baz"
    set req.url = regsuball(req.url,"\?((utm)|(mc))_[^=]+=[^&]+&","?"); # strips when QS = "?utm_xxxx=AAA&foo=bar"
    set req.url = regsuball(req.url,"\?((utm)|(mc))_[^=]+=[^&]+$",""); # strips when QS = "?utm_xxxxx=AAA"

    set req.url = regsuball(req.url,"&(back|max)=[^&]+","");
    set req.url = regsuball(req.url,"\?(back|max)=[^&]+&","?");
    set req.url = regsuball(req.url,"\?(back|max)=[^&]+$","");

    set req.url = regsuball(req.url,"&=(undefined)","");
    set req.url = regsuball(req.url,"\?=(undefined)&","?");
    set req.url = regsuball(req.url,"\?=(undefined)$","");


    return (hash);
}

sub vcl_hash {
    hash_data(req.url);
    if (req.http.host) {
        hash_data(req.http.host);
    } else {
        hash_data(server.ip);
    }

    if (!(req.url ~ "^/(media|js|skin)/.*\.(png|jpg|jpeg|gif|css|js|swf|ico)$")) {
        call design_exception;
    }
    hash_data(req.http.X-Currency);

    return (lookup);
}

sub vcl_backend_response {
    if (beresp.status == 500) {
       #set beresp.saintmode = 10s;
       return (retry);
    }
    set beresp.grace = 1h;
    set beresp.http.X-Purge-URL = bereq.url;
    set beresp.http.X-Purge-Host = bereq.http.host;

    if (beresp.status == 200 || beresp.status == 301 || beresp.status == 404 || beresp.status == 302) {
        if (beresp.http.Content-Type ~ "text/html" || beresp.http.Content-Type ~ "text/xml") {
	    if (beresp.ttl < 1s) {
                set beresp.ttl = 0s;
                #return (hit_for_pass);
                set beresp.uncacheable = true;
                return (deliver);
            }

            # marker for vcl_deliver to reset Age:
            set beresp.http.magicmarker = "1";

            # Don't cache cookies
            unset beresp.http.set-cookie;
        }else{
	        set beresp.ttl = 24h;
        }

        return (deliver);
    }

    #return (hit_for_pass);
    set beresp.uncacheable = true;
    return (deliver);
}

sub vcl_deliver {

    # debug info
    if (resp.http.X-Cache-Debug) {
        if (obj.hits > 0) {
           set resp.http.X-Cache = "HIT";
           set resp.http.X-Cache-Hits = obj.hits;
        } else {
           set resp.http.X-Cache = "MISS";
        }
        set resp.http.X-Cache-Expires = resp.http.Expires;
    } else {
        # unset Varnish/proxy header
        # unset resp.http.X-Varnish;
        unset resp.http.Via;
        unset resp.http.Age;
        unset resp.http.X-Purge-URL;
        unset resp.http.X-Purge-Host;
    }

	    # add ban-lurker tags to object
	    set resp.http.X-Purge-URL = req.url;
	    set resp.http.X-Purge-Host = req.http.host;

	    # fix cache per browser issue
	    set resp.http.Vary = "Accept-Encoding";
	    set resp.http.X-Country-Code = req.http.X-Country-Code;

	# Add in hit and miss markers
	if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
    	set resp.http.X-Cache-Hits = obj.hits;
    } else {
       set resp.http.X-Cache = "MISS";
    }

    if (resp.http.magicmarker) {
        # Remove the magic marker
        unset resp.http.magicmarker;

        set resp.http.Cache-Control = "no-store, no-cache, must-revalidate, post-check=0, pre-check=0";
        set resp.http.Pragma = "no-cache";
        set resp.http.Expires = "Mon, 31 Mar 2008 10:00:00 GMT";
        set resp.http.Age = "0";
    }
}

sub design_exception {

}

sub vcl_synth {
    if (resp.status == 250) { #for bots
        set resp.http.Content-Type = "text/html; charset=utf-8";
        synthetic ("");
    }
}