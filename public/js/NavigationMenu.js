/**
 * Created by xuman on 16-3-24.
 */
$.fn.extend({
    NavOverMenu: function (options) {
        var me = $(this);
        var text = '.menu-text';
        var container = '.menu-container';
        var baseStyles = {
            'me': {
                'position': 'relative',
                'width': 80,
                'height': 50,
                'line-height': '50px'
            },
            'text': {
                'text-decoration': 'none',
                'color': 'white',
                'display': 'block',
                'height': '100%',
                'width': '100%',
                'text-align': 'center'
            },
            'textOver': {
                'color': '#ffb800',
                'background-color': '#000000'
            },
            'textOut': {
                'color': 'white',
                'background-color': '#2F3847'
            },
            'container': {
                'position': 'absolute',
                'width': 300,
                'height': 400,
                'background-color': '#000000',
                'display': 'none',
                'z-index': 2000
            }
        };
        if (options.styles && typeof options.styles == 'object') {
            baseStyles = $.extend(true, baseStyles, options.styles);
        }
        var width = options.width ? options.width : 0;
        var height = options.height ? options.height : 0;
        if (width) baseStyles.me.width = width;
        if (height) {
            baseStyles.me.height = height;
            baseStyles.me['line-height'] = height + "px";
        }
        var containerWidth = parseInt(options.containerWidth ? options.containerWidth : 0);
        var containerHeight = parseInt(options.containerHeight ? options.containerHeight : 0);
        if (containerHeight) baseStyles.container.height = containerHeight;
        if (containerWidth) baseStyles.container.width = containerWidth;
        if (options.alignPosition) {
            if (options.alignPosition == 'left') {
                baseStyles.container.left = 0;
            } else {
                baseStyles.container.right = 0;
            }
        }
        var name = options.name ? options.name : '';
        if (!me.html()) {
            var inHtml = '<a class="' + text.replace('.', '') + '" href="javascript:void(0)">#name#</a><div class="'
                + container.replace('.', '') + '"></div>';
            inHtml = inHtml.replace('#name#', name);

            me.empty().html(inHtml);
        } else if (name) {
            me.find(text).html(name);
        }
        me.css(baseStyles.me);
        me.find(text).css(baseStyles.text);
        me.find(container).css(baseStyles.container);
        me.mouseover(function (e) {
            me.find(container).show();
            me.find(text).css(baseStyles.textOver);
        });

        me.mouseout(function () {
            me.find(container).hide();
            me.find(text).css(baseStyles.textOut);
        });
    }
});

/*
//使用示例
$('.menu').NavOverMenu({
    //name: '菜单',
    alignPosition: 'right',
    width: 80,
    height: 50,
    containerWidth: 300,
    containerHeight: 400,
    styles: {
        text :{
         'color' : 'green'
         }
    }
});*/
