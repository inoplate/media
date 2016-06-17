var elixir = require('laravel-elixir');

elixir(function(mix){
    mix.coffee('media.coffee', 'public')
       .less('library/tile.less', 'public/library')
       .coffee('library/tile.coffee', 'public/library')
       .less('library/detail.less', 'public/library')
       .coffee('library/detail.coffee', 'public/library')
       .less('library/uploader.less', 'public/library')
       .coffee('library/uploader.coffee', 'public/library')
       .less('library/index.less', 'public/library')
       .less('library/finder.less', 'public/library')
       .coffee('library/index.coffee', 'public/library')
       .coffee('library/finder.coffee', 'public/library');

    mix.copy('resources/assets/vendor/fancybox/source', 'public/vendor/fancybox');

    mix.copy('resources/assets/vendor/flow.js/dist', 'public/vendor/flowjs');
    mix.copy('resources/assets/vendor/jquery-load-template/dist', 'public/vendor/jquery-load-template');
    
    mix.copy('resources/assets/vendor/holderjs/holder.js', 'public/vendor/holderjs/holder.js');
    mix.copy('resources/assets/vendor/holderjs/holder.min.js', 'public/vendor/holderjs/holder.min.js');

    mix.copy('resources/assets/vendor/jquery_lazyload/jquery.lazyload.js', 'public/vendor/jquery_lazyload/jquery.lazyload.js');
    mix.copy('resources/assets/vendor/jquery_lazyload/jquery.scrollstop.js', 'public/vendor/jquery_lazyload/jquery.scrollstop.js');

    mix.copy('resources/assets/vendor/mediaelement/build', 'public/vendor/mediaelement');
})