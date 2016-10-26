var config = {
    'map': {
        '*': {
            'balance/box': 'Balance_Box/js/box',
        }
    },
    'shim': {
        'flickity': ['jquery']
    },
    'paths': {
        // flickity.pkgd.orig.js as supplied by vendor, below file
        // is patched for Magneto. Diff these to see patch.
        'flickity': 'Balance_Box/js/vendor/flickity.pkgd'
    }
};
