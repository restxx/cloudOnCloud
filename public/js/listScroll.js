/**
 * Created by lijun1 on 16-7-7.
 */
$.extend({
    roll: function (contentCls, contentParentId, configs) {

        var setTimeID, totalWidth = 0, totalHeight = 0,
            firstContent, secondContent, contents,
            singleContent, cloneContent, nodeList;

        singleContent = $(contentCls, contentParentId);
        nodeList = singleContent.children();

        if (configs.effect === 'scrollX') {
            $.each(nodeList, function (idx, itm) {
                totalWidth += $(itm).outerWidth(true);
            });
            singleContent.css({ 'width': totalWidth + 'px' });
        }
        else if (configs.effect === 'scrollY') {
            $.each(nodeList, function (idx, itm) {
                totalHeight += $(itm).outerHeight(true);
            });
            singleContent.css({ 'height': totalHeight + 'px' });
        }

        cloneContent = singleContent.clone();
        cloneContent.appendTo(contentParentId);

        contents = $(contentCls, contentParentId);
        firstContent = contents[0];
        secondContent = contents[1];


        if (configs.effect === 'scrollX') {
            $(firstContent).css({ 'left': 0 });
            $(secondContent).css({ 'left': totalWidth + 'px' });
        }
        else if (configs.effect === 'scrollY') {
            $(firstContent).css({ 'top': 0 });
            $(secondContent).css({ 'top': totalHeight + 'px' });
        }

        function cssAnimate() {
            if (configs.effect === 'scrollX') {
                $(firstContent).css({ left: parseInt(firstContent.style.left, 10) - 1 + 'px' });
                $(secondContent).css({ left: parseInt(secondContent.style.left, 10) - 1 + 'px' });

                $.each(contents, function (idx, itm) {
                    if (parseInt(itm.style.left, 10) === -totalWidth) {
                        $(itm).css({ left: totalWidth + 'px' });
                    }
                });
            }

            else if (configs.effect === 'scrollY') {
                $(firstContent).css({ top: parseInt(firstContent.style.top, 10) - 1 + 'px' });
                $(secondContent).css({ top: parseInt(secondContent.style.top, 10) - 1 + 'px' });

                $.each(contents, function (idx, itm) {
                    if (parseInt(itm.style.top, 10) === -totalHeight) {
                        $(itm).css({ top: totalHeight + 'px' });
                    }
                });
            }

            setTimeId = setTimeout(cssAnimate, configs.duration);
        }

        function rollRun() {
            setTimeId = setTimeout(cssAnimate, configs.delay);
            return jQuery;
        }

        function rollStop() {
            clearTimeout(setTimeId);
            return jQuery;
        }

        return $.extend({
            rollRun: rollRun,
            rollStop: rollStop
        });
    }
});