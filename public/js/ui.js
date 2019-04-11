/**
 * Created by lijun1 on 16-5-4.
 */

var Ui = {};

(function (Ui) {
    Ui.table = {
        instance: [],
        init: function (dom, Parmas) {
            Parmas = $.extend({
                bProcessing: true,
                bServerSide: true,
                bStateSave: false,
                sPaginationType: "full_numbers",
                bFilter: true,
                bSearchable: false,
                bSort: true,
                bAutoWidth: true,
                sDom: '<"clear">lrtip',
                aLengthMenu: [
                    [20, 50, 100],
                    [20, 50, 100]
                ],
                responsive: true,
                oLanguage: {
                    oPaginate: {
                        sFirst: "首页",
                        sPrevious: "前一页",
                        sNext: "后一页",
                        sLast: "尾页"
                    },
                    sLengthMenu: "显示 _MENU_ 条目",
                    sInfo: "从 _START_ 到 _END_ /共 _TOTAL_ 条数据",
                    sLoadingRecords: "加载中",
                    sProcessing: "<img src='/img/loading.gif'>",
                    sZeroRecords: '没有检索到数据',
                    sInfoFiltered: "(从 _MAX_ 条数据中检索)",
                    sInfoEmpty: "总共有 _TOTAL_ 条记录",
                    sSearch: "搜索:"
                },
                sServerMethod: "POST"
            }, Parmas);

            this.instance[dom.selector] = dom.dataTable(Parmas);

            dom.closest(".box-content").find("[aria-labelledby=typeMenu]").on("click", "li >a", function () {
                var target = $(this);
                Ui.table.instance[dom.selector].fnFilter(target.attr("data-value"), target.closest("[aria-labelledby=typeMenu]").attr('data-column'));
            });

            dom.closest(".box-content").find('input .column_filter').on('keypress', function () {
                if (event.keyCode == "13") {
                    var target = $(this);
                    Ui.table.instance[dom.selector].fnFilter(target.val(), target.attr('data-column'));
                }
            });

            dom.closest(".box-content").find('select .column_filter').on('change', function () {
                var target = $(this);
                Ui.table.instance[dom.selector].fnFilter(target.val(), target.attr('data-column'));

            });

            dom.closest(".panel-body").find("[aria-labelledby=typeMenu]").on("click", "li >a", function () {
                var target = $(this);
                Ui.table.instance[dom.selector].fnFilter(target.attr("data-value"), target.closest("[aria-labelledby=typeMenu]").attr('data-column'));
            });

            dom.closest(".panel-body").find('input.column_filter').on('keypress', function () {
                if (event.keyCode == "13") {
                    var target = $(this);
                    Ui.table.instance[dom.selector].fnFilter(target.val(), target.attr('data-column'));
                }
            });

            dom.closest(".panel-body").find('select.column_filter').on('change', function () {
                var target = $(this);
                Ui.table.instance[dom.selector].fnFilter(target.val(), target.attr('data-column'));

            });
        }
    };
})(Ui);
