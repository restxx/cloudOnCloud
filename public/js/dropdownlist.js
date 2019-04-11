/**
 * Created by xuman on 16-3-16.
 */
function getElementViewLeft(element) {
    var actualLeft = element.offsetLeft;
    var current = element.offsetParent;
    while (current !== null) {
        actualLeft += current.offsetLeft;
        current = current.offsetParent;
    }
    if (document.compatMode == "BackCompat") {
        var elementScrollLeft = document.body.scrollLeft;
    } else {
        var elementScrollLeft = document.documentElement.scrollLeft;
    }
    return actualLeft - elementScrollLeft;
}
function getElementViewTop(element) {
    var actualTop = element.offsetTop;
    var current = element.offsetParent;
    while (current !== null) {
        actualTop += current.offsetTop;
        current = current.offsetParent;
    }
    if (document.compatMode == "BackCompat") {
        var elementScrollTop = document.body.scrollTop;
    } else {
        var elementScrollTop = document.documentElement.scrollTop;
    }
    return actualTop - elementScrollTop;
}
function getElementLeft(element) {
    return getElementViewLeft(element);
}

function getElementTop(element) {
    return getElementViewTop(element);
}
$.fn.extend(
    {
        DropDownList: function (data, fn, selectedDefault) {
            var me = $(this);

            var init = false;
            var selectedClass = 'list-item-selected';
            var closed = "glyphicon glyphicon-menu-down";
            var opened = "glyphicon glyphicon-menu-up";
            var baseClassName = 'drop-list';
            var baseClass = '.' + baseClassName;
            me.find(baseClass).remove();
            var list = $('<ul>').addClass(baseClassName).hide();
            $.each(data, function (i, n) {
                var className = '';
                if (!init) {
                    me.html('<span>' + n + '</span> <span class="' + closed + '"></span>');
                    me.attr('data-value', i);
                    init = true;
                    className = selectedClass;
                }
                var item = '<li class="' + className + '" data-id="' + i + '">' + n + '</li>';
                item = $(item);
                list.append(item);
                item.click(function () {
                    var target = $(this);
                    var id = target.attr('data-id');
                    var name = target.html();
                    if (me.attr('data-value') != id) {
                        me.find('li').removeClass(selectedClass);
                        target.addClass(selectedClass);
                        me.attr('data-value', id);
                        me.find('span:eq(0)').html(name);
                        fn && fn(id, name);
                    }

                })
            });
            me.append(list);
            if(selectedDefault) {
                list.find('li[data-id='+selectedDefault+']').click();
            }
            me.unbind().click(
                function (e) {
                    e.stopPropagation();
                    if (me.find(baseClass).length > 0) {
                        if (me.find(baseClass + ':visible').length > 0) {
                            $(baseClass).hide();
                            $('body').find(baseClass).parent('div').find('span:eq(1)').removeClass(opened).addClass(closed);
                            me.find('span:eq(1)').removeClass(opened).addClass(closed);
                        } else {
                            $(baseClass).hide();
                            $('body').find(baseClass).parent('div').find('span:eq(1)').removeClass(opened).addClass(closed);
                            me.find(baseClass).show();
                            me.find('span:eq(1)').removeClass(closed).addClass(opened);
                        }
                    }
                }
            );
            $('body').click(function () {
                $(this).find(baseClass).hide();
                $(this).find(baseClass).parent('div').find('span:eq(1)').removeClass(opened).addClass(closed);
            });
        }
    }
);
$.fn.extend({
    DropMenu: function (list, statusList) {
        var body = $('body');
        var me = (this);
        if(me.attr('dropMenu-toggle'))
        {
            return;
        }
        me.attr('dropMenu-toggle', 'on');
        var ul = $("<ul>");
        ul.addClass('drop-menu menu-hide');
        me.append(ul);
        $.each(list, function (i, n) {
            var item = $('<li>');
            if (!statusList[i]) {
                item.addClass('disabled');
            }
            item.append($('<a>').html(n.name));
            item.click(function () {
                if ($(this).hasClass('disabled')) {
                    return;
                }
                n.func && n.func(me);
            });
            ul.append(item);
        });

        me.css('position', 'relative');
        ul.css('right', 0);
        me.unbind().click(function (e) {
            e.stopPropagation();
            var me = $(this);
            var all = $('.drop-menu');
            for(var i=1;i<all.length;i++)
            {
                if(all[i] == me.find('ul')[0])
                {
                    continue;
                }
                var item = $(all[i]);
                if(!item.hasClass('menu-hide')){
                    item.addClass('menu-hide');
                }
            }
            if (me.find('ul').hasClass('menu-hide')) {
                me.find('ul').removeClass('menu-hide');
            } else {
                me.find('ul').addClass('menu-hide');
            }
        });
        body.click(function () {
            $('.drop-menu').addClass('menu-hide');
        });
    }
});