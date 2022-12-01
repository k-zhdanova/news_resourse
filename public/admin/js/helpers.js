"use strict";

function renderMustache(target, templatePath, data, position) {
  var positionValues = ['overwrite', 'append', 'prepend'];
  position = isUndefined(position) || !inArray(position, positionValues) ? 'overwrite' : position;

  if (typeof templatePath === 'string') {
    templatePath = templatePath.split('.');
  } else if (!isArray(templatePath)) {
    throw new TypeError('Param "templatePath" should be array or point-separated string.');
  }

  var template = templates;
  templatePath.forEach(function (level) {
    if (!template.hasOwnProperty(level)) {
      throw new ReferenceError('Template "' + templatePath.join(".") + '" doesn\'t exist.');
    }

    template = template[level];
  });
  var content = Mustache.render(template, data);

  if (target != 'var') {
    var els = document.querySelectorAll(target);

    for (var i = 0; i < els.length; i++) {
      var element = els[i];

      if (position === 'prepend') {
        element.insertAdjacentHTML('afterbegin', content);
      } else if (position === 'append') {
        element.insertAdjacentHTML('beforeend', content);
      } else {
        element.innerHTML = content;
      }
    }
  }

  return content;
}

function isUndefined(val) {
  return typeof val === 'undefined' ? true : false;
}

function inArray(needle, haystack) {
  if (!Array.isArray(haystack)) return false;
  return haystack.indexOf(needle) !== -1 ? true : false;
}

function formatDate(date, format) {
  if (date) {
    date = date.replace(' ', 'T');
    date = new Date(date);
    var monthNames = ["янв", "фев", "мар", "апр", "мая", "июн", "июл", "авг", "сен", "окт", "ноя", "дек"];
    var day = date.getDate();
    var monthIndex = date.getMonth();
    var year = date.getFullYear();
    var hours = date.getHours();
    var minutes = date.getMinutes();
    minutes = String(minutes).length == 1 ? '0' + minutes : minutes;
    hours = String(hours).length == 1 ? '0' + hours : hours;

    if (format && format == 'date-only') {
      return day + ' ' + monthNames[monthIndex] + ' ' + year;
    }

    return day + ' ' + monthNames[monthIndex] + ' ' + year + ', ' + hours + ':' + minutes;
  }

  return date;
}

function getYoutubeID(url) {
  var ID = null;
  url = url.replace(/(>|<)/gi, '').split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/|\/shorts\/)/);

  if (url[2] !== undefined) {
    ID = url[2].split(/[^0-9a-z_\-]/i);
    ID = ID[0];
  } else {
    ID = typeof url == 'string' ? url : null;
  }

  return ID;
}

function hashCode(str) {
  var hash = 0,
      i,
      chr;
  if (str.length === 0) return hash;

  for (i = 0; i < str.length; i++) {
    chr = str.charCodeAt(i);
    hash = (hash << 5) - hash + chr;
    hash |= 0;
  }

  return Math.abs(hash);
}