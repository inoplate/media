var elixir = require('laravel-elixir');

elixir(function(mix){
    mix.less('library/uploader.less', 'public/library')
       .coffee('library/uploader.coffee', 'public/library')
       .less('library/index.less', 'public/library')
       .coffee('library/index.coffee', 'public/library');

    mix.copy('resources/assets/vendor/flow.js/dist', 'public/vendor/flowjs');
    
    mix.copy('resources/assets/vendor/holderjs/holder.js', 'public/vendor/holderjs/holder.js');
    mix.copy('resources/assets/vendor/holderjs/holder.min.js', 'public/vendor/holderjs/holder.min.js');
})