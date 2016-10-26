vcl 4.0;

import std;
# The minimal Varnish version is 4.0

backend default {
    .host = "${NGINXPROXY_PORT_80_TCP_ADDR}";
    .port = "8080";
    .connect_timeout = 18000s;
    .first_byte_timeout = 18000s;
    .between_bytes_timeout = 18000s;
}

#acl purge {
#    "localhost";
#}

sub vcl_recv {

    #uncomment this to affectively disable varnish
    return(pass);

    # Always pass for ELB healthcheck
    if (req.url ~ "healthcheck.php") {
        return(pass);
    }


    # Allow Blackfire queries through varnish
    if (req.http.X-Blackfire-Query) {
        return (pass);
    }

    # Always pass for cloudfront requests as cloudfront is affectively the cache in this case
    if (req.http.user-agent ~ "Amazon CloudFront") {
        return (pass);
    }

     # do not cache any page has url param no_cache
    if (req.url ~ "NO_CACHE") {
        return (pass);
    }

    if (req.method == "PURGE") {
        # if (client.ip !~ purge) {
        #     return (synth(405, "Method not allowed"));
        # }
        if (!req.http.X-Magento-Tags-Pattern) {
            return (synth(400, "X-Magento-Tags-Pattern header required"));
        }
        #ban("obj.http.X-Magento-Tags ~ " + req.http.X-Magento-Tags-Pattern);
	# Flush everything instead of only Magento Cache Tags
        ban("req.url ~ /");
        return (synth(200, "Purged"));
    }

    if (req.method != "GET" &&
        req.method != "HEAD" &&
        req.method != "PUT" &&
        req.method != "POST" &&
        req.method != "TRACE" &&
        req.method != "OPTIONS" &&
        req.method != "DELETE") {
          /* Non-RFC2616 or CONNECT which is weird. */
          return (pipe);
    }

    # We only deal with GET and HEAD by default
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

   set req.backend_hint = default;


    # normalize url in case of leading HTTP scheme and domain
    set req.url = regsub(req.url, "^http[s]?://", "");

    # collect all cookies
    std.collect(req.http.Cookie);

    # static files are always cacheable. remove SSL flag and cookie
        if (req.url ~ "^/(pub/)?(media|static)/.*\.(ico|css|js|jpg|jpeg|png|gif|tiff|bmp|mp3|ogg|svg|swf|woff|woff2|eot|ttf|otf)$") {
        unset req.http.Https;
        unset req.http.Cookie;
    }

    set req.url = regsuball(req.url,"\?gclid=[^&]+$",""); # strips when QS = "?gclid=AAA"
    set req.url = regsuball(req.url,"\?gclid=[^&]+&","?"); # strips when QS = "?gclid=AAA&foo=bar"
    set req.url = regsuball(req.url,"&gclid=[^&]+",""); # strips when QS = "?foo=bar&gclid=AAA" or QS = "?foo=bar&gclid=AAA&bar=baz"

    set req.url = regsuball(req.url,"&((utm)|(mc))_[^=]+=[^&]+",""); # strips when QS = "?foo=bar&utm_xxxx=AAA" or QS = "?foo=bar&utm_xxxx=AAA&bar=baz"
    set req.url = regsuball(req.url,"\?((utm)|(mc))_[^=]+=[^&]+&","?"); # strips when QS = "?utm_xxxx=AAA&foo=bar"
    set req.url = regsuball(req.url,"\?((utm)|(mc))_[^=]+=[^&]+$",""); # strips when QS = "?utm_xxxxx=AAA"

    return (hash);
}

sub vcl_hash {
    if (req.http.cookie ~ "X-Magento-Vary=") {
        hash_data(regsub(req.http.cookie, "^.*?X-Magento-Vary=([^;]+);*.*$", "\1"));
    }

    # cache http and https requests separately
    if (req.http.X-Forwarded-Proto) {
        hash_data(req.http.X-Forwarded-Proto);
    }
}

sub vcl_backend_response {
    if (beresp.http.content-type ~ "text") {
        set beresp.do_esi = true;
    }

    if (bereq.url ~ "\.js$" || beresp.http.content-type ~ "text") {
        set beresp.do_gzip = true;
    }

    # cache only successfully responses
    if (beresp.status != 200) {
        set beresp.ttl = 0s;
        set beresp.uncacheable = true;
        return (deliver);
    } elsif (beresp.http.Cache-Control ~ "private") {
        set beresp.uncacheable = true;
        set beresp.ttl = 86400s;
        return (deliver);
    }

    if (beresp.http.X-Magento-Debug) {
        set beresp.http.X-Magento-Cache-Control = beresp.http.Cache-Control;
    }

    # validate if we need to cache it and prevent from setting cookie
    # images, css and js are cacheable by default so we have to remove cookie also
    if (beresp.ttl > 0s && (bereq.method == "GET" || bereq.method == "HEAD")) {
        unset beresp.http.set-cookie;
        if (bereq.url !~ "\.(ico|css|js|jpg|jpeg|png|gif|tiff|bmp|gz|tgz|bz2|tbz|mp3|ogg|svg|swf|woff|woff2|eot|ttf|otf)(\?|$)") {
            set beresp.http.Pragma = "no-cache";
            set beresp.http.Expires = "-1";
            set beresp.http.Cache-Control = "no-store, no-cache, must-revalidate, max-age=0";
            set beresp.grace = 1m;
        }
    }
    return (deliver);
}

sub vcl_deliver {
    if (resp.http.X-Magento-Debug) {
        if (resp.http.x-varnish ~ " ") {
            set resp.http.X-Magento-Cache-Debug = "HIT";
            set resp.http.X-Cache-Hits = obj.hits;
        } else {
            set resp.http.X-Magento-Cache-Debug = "MISS";
        }
    } else {
        unset resp.http.Age;
    }

    unset resp.http.X-Magento-Debug;
    unset resp.http.X-Magento-Tags;
    unset resp.http.X-Powered-By;
    unset resp.http.Server;
    unset resp.http.X-Varnish;
    unset resp.http.Via;
    unset resp.http.Link;
}

