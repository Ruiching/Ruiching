var pageTools = function () {

    var ajaxPost = function () {
        jQuery(document).delegate(".ajax-post", "click", function () {
            var self = $(this);
            var url = self.data("url");
            var title = self.data("title") || "确定要执行该操作吗？";
            var text = self.data("tips");
            var form = jQuery('form[name="' + self.data("form") + '"]');
            var data = form.serialize();
            if (self.hasClass("confirm")) {
                swal({
                    title: title,
                    text: text,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn btn-danger m-1",
                    cancelButtonClass: "btn btn-secondary m-1",
                    confirmButtonText: "确定",
                    cancelButtonText: "取消",
                    html: false
                }).then(function (e) {
                    if (e.value) {
                        One.layout('header_loader_on');
                        jQuery.ajax({
                            type: "POST",
                            url: url,
                            data: data,
                            success: function (data) {
                                One.layout('header_loader_off');
                                if (data.code == 1) {
                                    var msg = data.msg;
                                    if (data.url && !self.hasClass("no-refresh")) {
                                        msg += " 页面即将自动跳转~";
                                    }
                                    One.helpers("notify", {message: msg, align: "center", "type": "success"});
                                    setTimeout(function () {
                                        return self.hasClass("no-refresh") ? false : void(data.url && !self.hasClass("no-forward") ? location.href = data.url : location.reload());
                                    }, 1200);
                                } else {
                                    One.helpers("notify", {message: data.msg, align: "center", "type": "warning"});
                                }
                            },
                            fail: function () {
                                One.layout('header_loader_off');
                                One.helpers("notify", {message: "系统出错", align: "center", "type": "error"});
                            }
                        });
                    }
                })
            } else {
                One.layout('header_loader_on');
                jQuery.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    success: function (data) {
                        One.layout('header_loader_off');
                        if (data.code == 1) {
                            var msg = data.msg;
                            if (data.url && !self.hasClass("no-refresh")) {
                                msg += " 页面即将自动跳转~";
                            }
                            One.helpers("notify", {message: msg, align: "center", "type": "success"});
                            setTimeout(function () {
                                return self.hasClass("no-refresh") ? false : void(data.url && !self.hasClass("no-forward") ? location.href = data.url : location.reload());
                            }, 1200);
                        } else {
                            One.helpers("notify", {message: data.msg, align: "center", "type": "warning"});
                        }
                    },
                    fail: function () {
                        One.layout('header_loader_off');
                        One.helpers("notify", {message: "系统出错", align: "center", "type": "error"});
                    }
                });
            }
        });
    };

    var ajaxGet = function () {
        jQuery(document).delegate(".ajax-get", "click", function () {
            var self = $(this);
            var url = self.attr("href") || self.data("url");
            console.log(url)
            var title = self.data("title") || "确定要执行该操作吗？";
            var text = self.data("tips");
            if (self.hasClass("confirm")) {
                swal({
                    title: title,
                    text: text,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn btn-danger m-1",
                    cancelButtonClass: "btn btn-secondary m-1",
                    confirmButtonText: "确定",
                    cancelButtonText: "取消",
                    html: false
                }).then(function (e) {
                    if (e.value) {
                        One.layout('header_loader_on');
                        jQuery.ajax({
                            type: "POST",
                            url: url,
                            data: {},
                            success: function (data) {
                                One.layout('header_loader_off');
                                if (data.code == 1) {
                                    var msg = data.msg;
                                    if (data.url && !self.hasClass("no-refresh")) {
                                        msg += " 页面即将自动跳转~";
                                    }
                                    One.helpers("notify", {message: msg, align: "center", "type": "success"});
                                    setTimeout(function () {
                                        return self.hasClass("no-refresh") ? false : void(data.url && !self.hasClass("no-forward") ? location.href = data.url : location.reload());
                                    }, 1200);
                                } else {
                                    One.helpers("notify", {message: data.msg, align: "center", "type": "warning"});
                                }
                            },
                            error: function () {
                                One.layout('header_loader_off');
                                One.helpers("notify", {message: "系统出错", align: "center", "type": "danger"});
                            }
                        });
                    }
                })
            }
        });
    };

    return {
        init: function () {
            ajaxGet();
            ajaxPost();
        }
    }
}();

$(function () {
    pageTools.init();

    // switch开关
    $(".table .custom-switch .custom-control-input").on("click", function () {
        var ele = $(this);
        var url = ele.data("url");
        var reverse = ele.data("reverse") || 0;
        var value = 0;
        if (reverse) {
            value = ele.prop("checked") ? 0 : 1;
        } else {
            value = ele.prop("checked") ? 1 : 0;
        }
        var data = {
            id: ele.data("id") || 0,
            field: ele.data("field") || "",
            value: value
        };
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function (data) {
                if (data.code == 1) {
                    One.helpers("notify", {message: data.msg, align: "center", "type": "success"});
                } else {
                    ele.prop("checked", !ele.prop("checked"));
                    One.helpers("notify", {message: data.msg, align: "center", "type": "warning"});
                }
            },
            error: function () {
                ele.prop("checked", !ele.prop("checked"));
                One.helpers("notify", {message: "系统出错", align: "center", "type": "danger"});
            }
        });
    });

    // btn跳转
    $("button.go-page").on("click", function () {
        location.href = $(this).data("url");
    });
});