"use strict";

App.events = function () {
  $('body').on('change', '.edit-form :input:not(.select2):not(.ajax-select):not(.groupEditorItem)', function (e) {
    $('.edit-form').data("changed", true);
  });
  $('body').on('change', '.summernote-light', function (e) {
    $('.edit-form').data("changed", true);
  });
  $(window).on('beforeunload', function (e) {
    if (App.st.action == 'edit' || App.st.action == 'create') {
      if ($(".edit-form").data("changed") && App.user.scopes[App.st.cntrl].update) {
        var confirmationMessage = 'Ви не зберегли зміни.' + 'Якщо ви не збережете зміни, вони можуть бути втрачені.';
        (e || window.event).returnValue = confirmationMessage; //Gecko + IE

        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
      }
    }
  });
  $('body').on('click', App.controls.listBtn, function (e) {
    e.preventDefault();

    if ($(".edit-form").data("changed") && App.user.scopes[App.st.cntrl].update) {
      var swal = Swal.mixin({
        customClass: {
          confirmButton: 'btn btn-outline-success mr-2',
          cancelButton: 'btn btn-outline-secondary'
        },
        buttonsStyling: false
      });
      swal.fire({
        title: "Увага!",
        text: "Залишилися незбережені зміни.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Зберегти та вийти",
        cancelButtonText: "Вийти",
        allowOutsideClick: false
      }).then(function (result) {
        console.log(result);

        if (result.value) {
          var form = $('.edit-form');

          if (form[0].checkValidity() === false) {
            form.addClass('was-validated');
          } else {
            var id = $('.save-btn').attr('data-id'),
                data = $(App.controls.editForm).serialize();
            $('.save-btn').addClass('disabled');
            $('.preview-btn').addClass('disabled');
            App.editElem(id, data, function () {
              $('.edit-form').data("changed", false);
              $('#content').find('.list-btn').first().click();
            });
          }
        } else {
          $('.edit-form').data("changed", false);
          $('#content').find('.list-btn').first().click();
        }
      });
    } else {
      var hash = $(this).attr('href') ? $(this).attr('href') : $(this).attr('data-route');
      location.hash = hash;
      App.route();
      App.st.search = $('.app-search input').val();

      if (App.st.cntrl.indexOf('constructor') > -1) {
        App.initConstructor();
      } else if (App.st.cntrl.indexOf('stat') > -1) {
        App.initStats();
      } else {
        App.initListView(App.st.page);
      }

      App.initActive();
    }
  });
  $('body').on('click', App.controls.addBtn, function (e) {
    e.preventDefault();

    if ($(this).attr('data-param') && $(this).attr('data-value')) {
      var params = $(this).attr('data-param').split(',');
      var values = $(this).attr('data-value').split(',');
      App.tmp.create = {};
      $(params).each(function (index, param) {
        if (param && values[index]) {
          App.tmp.create[param] = values[index];
        }
      });
    }

    location.hash = $(this).attr('data-route');
    App.route();
    App.initActive();
    App.initEditView();
  });
  $('body').on('click', App.controls.editBtn, function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    var cntrl = $(this).attr('data-cntrl');
    cntrl = cntrl ? cntrl : App.st.cntrl;
    location.hash = '#' + cntrl + '-edit-' + id;
    App.route();
    App.initActive();
    App.initEditView(id);
  });
  $('body').on('click', App.controls.publishBtn, function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    var status = $(this).attr('data-status');
    var name = $(this).attr('data-name');
    var data = "status=".concat(status, "&publishing=1&name:uk=").concat(name);
    App.editElem(id, data, App.initListView);
  });
  $('body').on('click', App.controls.cancelBtn, function (e) {
    e.preventDefault();
    location.hash = $(this).attr('data-route');
    App.route();
    App.initActive();
    App.initEditView(App.st.id);
  });
  $('body').on('click', App.controls.viewBtn, function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    location.hash = '#' + App.st.cntrl + '-view-' + id;
    App.route();
    App.initActive();
    App.initEditView(id, 'preview');
  }); // $('body').on('click', App.controls.copyBtn, function(e){
  // 	e.preventDefault();
  // 	var id = $(this).attr('data-id');
  // 	App.copyElem(id);
  // });

  $('body').on('change', 'input[name="datetime"]', function (e) {
    var datetime = $(e.target).val().split('-');
    $('input[name="month"]').val(datetime[0]);
    $('input[name="year"]').val(datetime[1]);
  });
  $('body').on('click', App.controls.saveBtn, function (e) {
    e.preventDefault();
    var form = $('.edit-form');

    if (form[0].checkValidity() === false) {
      form.addClass('was-validated');
      setTimeout(function () {
        if (App.st.cntrl == 'news') {
          $('.customizer').addClass('show-service-panel');
        }
      }, 300);
    } else {
      var id = $(this).attr('data-id'),
          data = $(App.controls.editForm).serialize();

      if (App.st.cntrl.indexOf('constructor') > -1) {
        App.editElem(id, data, App.initConstructor);
      } else {
        $('.save-btn').addClass('disabled');
        $('.preview-btn').addClass('disabled');
        App.editElem(id, data);
      }
    }
  });
  $('body').on('click', '.template-btn', function (e) {
    e.preventDefault();
    var id = $(this).data('id'),
        data = {
      st_cntrl: 'news',
      st_action: 'create'
    };
    App.st.templates.map(function (template) {
      if (template.id == id) {
        data.text = template.text;
      }
    });
    console.log(data);
    App.initCreateView(data);
  });
  $('body').on('click', '.preview-btn', function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id'),
        title = $('[name="name"]').val(),
        previewUrl = "".concat(window.location.origin, "/preview/").concat(id);
    var type = $(this).attr('data-type');

    if (type) {
      previewUrl = "".concat(window.location.origin, "/").concat(type, "/").concat(id);
    }

    if (App.st.action == 'view') {
      window.open(previewUrl, '_blank');
    } else {
      var form = $('.edit-form');

      if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
      } else {
        var data = $(App.controls.editForm).serialize();
        $('.save-btn').addClass('disabled');
        $('.preview-btn').addClass('disabled');
        App.editElem(id, data, function () {
          $('.edit-form').data("changed", false);
          window.open(previewUrl, '_blank');
        });
      }
    }
  });
  $('body').on('click', '.plan-btn', function (e) {
    e.preventDefault();
    var form = $('.edit-form');

    if (form[0].checkValidity() === false) {
      form.addClass('was-validated');
    } else {
      var id = $('[name="id"]').val(),
          data = $(App.controls.editForm).serialize();
      data += '&schedule=1';
      $('.plan-btn').addClass('disabled').attr('disabled', 'disabled');
      App.editElem(id, data);
    }
  });
  $('body').on('input', '[invalid]', function () {
    $(this).removeAttr('invalid');
    $(this).parent().removeClass('was-validated');
    this.setCustomValidity('');
  });
  $('body').on('change', 'select[invalid]', function () {
    $(this).removeAttr('invalid');
    $(this).parent().removeClass('was-validated');
    this.setCustomValidity('');
  });
  $('body').on('click', App.controls.deleteBtn, function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    var cntrl = $(this).attr('data-cntrl');
    var swal = Swal.mixin({
      customClass: {
        confirmButton: 'btn btn-outline-danger mr-2',
        cancelButton: 'btn btn-outline-secondary'
      },
      buttonsStyling: false
    });
    swal.fire({
      title: "Видалити запис?",
      text: "Ви не зможете відновити запис!",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Так, видалити!",
      cancelButtonText: "Ні, відмінити"
    }).then(function (result) {
      if (result.value) {
        App.deleteElem(id, cntrl);
      }
    });
  });
  $('body').on('click', '.review-toggler', function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id'),
        data = {};
    data[$(this).attr('data-toggle')] = true;
    App.updateReview(id, data);
  });
  $('body').on('click', App.controls.uploadImgBtn, function (e) {
    e.preventDefault();
    $('[name="image_delete"]').val(null);
    $(App.controls.uploadImgInput).click();
  });
  $('body').on('click', '.variant-image-btn', function (e) {
    e.preventDefault();
    $(this).parents('.variant').find('[name*="variantImageupload"]').click();
  });
  $('body').on('click', '.variant-image-delete-btn', function (e) {
    e.preventDefault();
    var parent = $(this).parents('.variant');
    parent.find('.variantImage').find('img').attr('src', 'assets/images/holder-landscape.jpg');
    parent.find('[name*="base64"]').val('');
    parent.find('[name*="image_delete"]').val(1);
  });
  $('body').on('click', App.controls.deleteImgBtn, function (e) {
    e.preventDefault();
    $('[name="image_delete"]').val(1);
    $(this).parents('.el-card-avatar').find('img').attr('src', 'assets/images/holder.jpg');
    $(App.controls.deleteImgBtn).hide();
    $('.image-popup').hide();
  });
  $('body').on('change', App.controls.uploadFileInput, function (e) {
    e.preventDefault();
    $(e.target).next('label').text($(e.target).val());

    if (this.files && this.files[0]) {
      var uploadFile = $(e.target).parent().find('.upload-file');
      var FR = new FileReader();
      FR.addEventListener("load", function (reader) {
        var src = reader.target.result;

        if (src.length) {
          $(uploadFile).val(src);
        }
      });
      FR.readAsDataURL(this.files[0]);
    }
  });
  $('body').on('change', App.controls.uploadImgInput, function (e) {
    e.preventDefault();

    if (this.files && this.files[0]) {
      var FR = new FileReader();
      FR.addEventListener("load", function (reader) {
        if ($(App.controls.uploadImg).length) {
          $(App.controls.uploadImg).val(reader.target.result);
          $(App.controls.uploadImgPreview).attr('src', reader.target.result);
        }

        if ($(App.controls.uploadImgs).length) {
          var images = $(App.controls.uploadImgs).val();
          images = images ? JSON.parse(images) : [];
          images = images.concat([reader.target.result]);
          images = JSON.stringify(images);
          $(App.controls.uploadImgs).val(images);
          var preview = renderMustache('var', App.st.cntrl + '.image', {
            image: reader.target.result
          });

          if (App.st.cntrl == 'problems') {
            $(App.controls.uploadImgBtn).parents('.col-md-4').before($(preview));
          } else {
            $(App.controls.uploadImgBtn).parents('.card.el-element-overlay').before($(preview));
          }
        }
      });
      FR.readAsDataURL(this.files[0]);
    }
  });
  $('body').on('change', '[name*="variantImageupload"]', function (e) {
    e.preventDefault();

    if (this.files && this.files[0]) {
      var FR = new FileReader();
      FR.addEventListener("load", function (reader) {
        var src = reader.target.result,
            parent = $(e.target).parents('.variant');
        parent.find('[name*="base64"]').val(reader.target.result);
        parent.find('.variantImage').find('img').attr('src', src);
        parent.find('.variant-image-delete-btn').fadeIn();
      });
      FR.readAsDataURL(this.files[0]);
    }
  });
  $('body').on('click', App.controls.calendarBtn, function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    location.hash = '#' + App.st.cntrl + '-calendar-' + id;
    App.route();
    App.initActive();
    App.initCalendarView(id);
  });
  $('body').on('change', '[name="place"]', function () {
    var data = {
      parking_id: $(this).attr('data-parking'),
      place: $(this).val()
    };
    App.reloadCalendarData(data);
  });
  $('body').on('click', '.fc-header-toolbar button', function () {
    $('[name="place"]:checked').trigger('change');
  });
  $('body').on('click', '#cities .dropdown-item', function (e) {
    e.preventDefault();
    var currentCity = $(this).text(),
        currentCityID = $(this).attr('data-id');
    localStorage.setItem('currentCity', currentCity);

    if (currentCityID) {
      localStorage.setItem('currentCityID', currentCityID);
    } else {
      localStorage.removeItem('currentCityID');
    }

    location.reload();
  });
  $('body').on('click', '.freeDraw-create', function (e) {
    e.preventDefault();
    App.initFreeDraw();
  });
  $('body').on('click', '.freeDraw-edit', function (e) {
    e.preventDefault();
    App.initFreeDraw();
  });
  $('body').on('click', '.freeDraw-save', function (e) {
    e.preventDefault();
    App.initFreeDraw();
  });
  $('body').on('click', '.freeDraw-delete', function (e) {
    e.preventDefault();
    App.deleteFreeDraw();
  });
  $('body').on('click', '.daterangepicker .applyBtn', function () {
    if ($('[name="custom-period"]').length) {
      var dates = $('[name="custom-period"]').val().split(' - ');
      App.st.dateFrom = moment(dates[0], HUMAN_DAY).format(HUMAN_DATE);
      App.st.dateTill = moment(dates[1], HUMAN_DAY).endOf('day').format(HUMAN_DATE);

      if (App.st.cntrl.indexOf('stats') > -1) {
        App.initStats();
      } else {
        App.initListView();
      }
    }
  });
  $('.app-search').on('submit', function (e) {
    e.preventDefault();
    App.st.search = $('.app-search input').val();

    if (App.st.action == 'list') {
      $('.list-btn[href="#' + App.st.cntrl + '"]').click();
    }
  });
  $('.srh-btn').on('click', function () {
    $('.app-search input').val('');

    if (App.st.search.length > 0) {
      App.st.search = '';

      if (App.st.action == 'list') {
        $('.list-btn[href="#' + App.st.cntrl + '"]').click();
      }
    }
  });
  $('body').on('click', '.ajax-collapse', function (e) {
    e.preventDefault();
    App.loadCollapseContent(e.currentTarget, $(e.currentTarget).attr('data-url'));
  });
  $('body').on('click', '.templates-collapse', function (e) {
    e.preventDefault();
    var el = e.currentTarget;

    if (App.st.templates.length) {
      renderMustache($(el).attr('href'), "".concat(App.st.cntrl, ".collapse"), {
        data: App.st.templates
      });
      $(el).hide().next().collapse();
    } else {
      App.loadCollapseContent(e.currentTarget, $(e.currentTarget).attr('data-url'));
    }
  });
  $('body').on('input', '[name="icon"]', function (e) {
    var svg = e.target.value;
    $('#iconPreview').html(svg);
  });
  $('body').on('input', '[name="noimage"]', function (e) {
    var svg = e.target.value;
    $('#iconPreview_1').html(svg);
  });
  $('body').on('click', '.action-btn', function (e) {
    var id = $(e.target).attr('data-id'),
        action = $(e.target).attr('data-action');
    var images = $(App.controls.uploadImgs).val();
    images = images ? JSON.parse(images) : [];
    var text = $('[name="' + action + '_text"]').val();
    var name = App.user.firstname;
    name += App.user.lastname ? ' ' + App.user.lastname : '';
    var swal = Swal.mixin({
      customClass: {
        confirmButton: 'btn btn-outline-success mr-2',
        cancelButton: 'btn btn-outline-secondary'
      },
      buttonsStyling: false
    });
    swal.fire({
      title: "Увага!",
      text: "Ви впевнені, що хочете надіслати це повідомлення автору цього запиту? Процес надсилання необоротний, тому уважно перевірте дані перед відправкою.",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Відправити",
      cancelButtonText: "Відмінити",
      allowOutsideClick: false
    }).then(function (result) {
      console.log(result);

      if (result.value) {
        App.createReview({
          'entry_id': id,
          'name': name,
          'text': text,
          'action': action,
          'images': images
        });
      }
    });
  });
  $('body').on('input', '[name="started_text"],[name="finished_text"],[name="refused_text"],[name="created_text"]', function (e) {
    var el = $(e.target);

    if (el.val().length > 5) {
      el.parents('.card-body').find('.action-btn').removeAttr('disabled');
    } else {
      el.parents('.card-body').find('.action-btn').attr('disabled', 'disabled');
    }
  });
  $('body').on('change', '[name="respond_text"]', function (e) {
    $(e.target).parents('.card-body').find('.action-btn').removeAttr('disabled');
  });
  $('body').on('change', '.event-repeater input[name="is_repeated"]', function (e) {
    if ($('.event-repeater input[name="is_repeated"]:checked').val() == '1') {
      $('.event-repeater .durations').children().removeAttr('disabled');
      $('.event-repeater .durations [data-value="1"]').focus();
    } else {
      $('.event-repeater .durations').children().attr('disabled', 'disabled');
    }
  });
  $('body').on('click', '.durations .btn', function (e) {
    $('.durations [name="duration"]').val($(e.target).attr('data-value'));
    $('.durations [name="customDuration"]').val('');
  });
  $('body').on('input', '[name="customDuration"]', function (e) {
    var val = $(e.target).val();

    if (val < 1) {
      val = 1;
      $('.durations [name="customDuration"]').val(val);
    }

    if (val > 12) {
      val = 12;
      $('.durations [name="customDuration"]').val(val);
    }

    $('.durations [name="duration"]').val(val);
  });
  $('body').on('change paste', '[name="video_link"]', function (e) {
    var url = $(e.target).val();
    var id = getYoutubeID(url);
    $('[name="video_code"]').val(id);
    $(e.target).next('iframe').removeClass('hidden');
    $(e.target).next('iframe').attr('src', "https://www.youtube.com/embed/".concat(id));
  });
  $('body').on('change', '[name^="videos"]', function (e) {
    var value = $(this).val();

    if (value.length) {
      var url = new URL(value);
      console.log(url);

      if (url.host == 'youtu.be') {
        url.host = 'www.youtube.com';
        url.pathname = '/embed' + url.pathname;
      }

      var searchParams = url.searchParams;
      var start = searchParams.get('start');
      start = start ? start : searchParams.get('t');

      if (url.pathname == '/watch') {
        url.pathname = '/embed/' + searchParams.get('v');
        url.search = '';
      }

      if (start) {
        url.search = '?start=' + start;
      }

      $(this).val(url.href);
    }
  });
  $('body').on('click', '.unpublish-btn', function (e) {
    e.preventDefault();
    $('[name="is_published"]').val(0);
    $(App.controls.saveBtn).first().click();
  });
  $('body').on('select2:select', '.collapse-select', function (e) {
    var target = $('.collapse-select').select2().find(":selected").data("target");
    $('.collapse').on('shown.bs.collapse', function () {
      if (target == 'categories' || target == 'quote') {
        App.loadAjaxSelect("#".concat(target, " .ajax-select"));
      } else {
        $("#".concat(target, " .select2:not(.ajax-select)")).select2();
      }
    });
    $('.collapse').each(function (k, el) {
      var id = $(el).attr('id');

      if (id == target) {
        $(el).collapse('show');
      } else {
        $(el).collapse('hide');
      }
    });
  });
  $('body').on('click', '.create-btn', function (e) {
    console.log($(e.target).attr('data-id'));
    var data = "imported_at=NULL&type=".concat($(e.target).attr('data-type'));

    var callback = function callback() {
      App.initListView();
    };

    App.editElem($(e.target).attr('data-id'), data, callback);
  });
};