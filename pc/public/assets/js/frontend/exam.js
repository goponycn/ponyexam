define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function($, undefined, Frontend, Form, Template) {
	var validatoroptions = {
		invalid: function(form, errors) {
			$.each(errors, function(i, j) {
				Layer.msg(j);
			});
		}
	};

	function savepaper() {
		var params = $("#paper").serialize();
		$.ajax({
			url: "/index/exam/save",
			async: true,
			type: 'post',
			dataType: 'json',
			data: params,
			error: function(err) { //如果确定能正确运行,可不写
				alert('试卷保存失败');
			},
		});
	}

	function gotoquestion(index) {
		$("body,html").animate({
			scrollTop: $('#q_' + index).offset().top - $('#header-navbar').height() - $("#questionTabs").height() - $(
				"#questionTabContent").height()
		}, 0);
	}


	function markpaper() {
		$('.qindex').removeClass("btn-success");
		$('.qindex').addClass("btn-default");
		$('#paper :checked').each(function() {
			var rel = $(this).attr('rel');
			$('#qs_' + rel).removeClass("btn-default");
			$('#qs_' + rel).addClass("btn-success");
		});
		$('#paper :text').each(function() {
			if ($(this).val().length > 0) {
				var rel = $(this).attr('rel');
				$('#qs_' + rel).removeClass("btn-default");
				$('#qs_' + rel).addClass("btn-success");
			}
		});
		$('#paper textarea').each(function() {
			if ($(this).val().length > 0) {
				var rel = $(this).attr('rel');
				$('#qs_' + rel).removeClass("btn-default");
				$('#qs_' + rel).addClass("btn-success");
			}
		});
	}

	var timer = function() {
		var h, m, s, t;
		var init = function() {
			lefttime = $("#lefttime").val();
			s = lefttime % 60;
			m = parseInt(lefttime % 3600 / 60);
			h = parseInt(lefttime / 3600);
		}

		var setval = function() {
			if (s >= 10)
				$("#timers").html(s);
			else
				$("#timers").html('0' + s);
			if (m >= 10)
				$("#timerm").html(m);
			else
				$("#timerm").html('0' + m);
			if (h >= 10)
				$("#timerh").html(h);
			else
				$("#timerh").html('0' + h);
		}

		var step = function() {
			if (s > 0) {
				s--;
			} else {
				if (m > 0) {
					m--;
					s = 60;
					s--;
				} else {
					if (h > 0) {
						h--;
						m = 60;
						m--;
						s = 60;
						s--;
					} else {
						clearInterval(interval);
						savepaper();
						return;
					}
				}
			}
			setval();
		}
		init();
		interval = setInterval(step, 1000);
	};

	var Controller = {
		page: function() {
			timer();
			markpaper();
			setInterval(savepaper, 60000);
			// 选取第一个标签页
			$('#questionTabs a:first').tab('show');

			$(document).on("click", "#finishpager", function() {
				var params = $("#paper").serialize();
				layer.confirm('确定要交卷吗？', {
					btn: ['确定', '取消']
				}, function() {
					layer.closeAll('dialog');
					$.ajax({
						url: "/index/exam/finish",
						async: true,
						type: 'post',
						dataType: 'json',
						data: params,
						success: function(data) {
							window.location.href = "/index/exam/exam";
						},
						error: function(err) { //如果确定能正确运行,可不写
							alert('交卷失败');
						},
					});
				})
			});

			$(document).on("click", ".qindex", function() {
				var index = $(this).attr('rel');
				gotoquestion(index);
			});

			$(document).on("click", ":radio", function() {
				markpaper();
			});
			$(document).on("click", ":checkbox", function() {
				markpaper();
			});
			$(document).on("change", ":text", function() {
				markpaper();
			});
			$(document).on("change", "textarea", function() {
				markpaper();
			});
			$(document).on("click", "#savepager", function() {
				savepaper();
			});

			//为表单绑定事件
			Form.api.bindevent($("#page"), function(data, ret) {
				setTimeout(function() {
					alert('交卷失败');

				}, 1000);
			});

		}
	};
	return Controller;
});
