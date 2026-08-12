(function () {
  'use strict';

  var form = document.getElementById('halopress-fx-settings-form');
  if (!form) {
    return;
  }

  var defaults = {
    enabled: true,
    click_enabled: true,
    trail_enabled: true,
    trail_always: false,
    mobile_enabled: true,
    theme_color: '#4ca7ff',
    scale: '1',
    opacity: '1',
    click_time_scale: '1',
    trail_time_scale: '1',
    quality_preset: 'balanced',
    effect_backend: 'webgl2',
    bloom_backend: 'webgl2',
    rendering_mode: 'enhanced',
    max_dpr: '1',
    theme_color_mode: 'hue-only',
    output_compositing: 'browser-overlay',
    overlay_alpha_policy: 'coverage',
    overlay_color_compensation: 'none',
    host_compositing: 'source-over',
    isolated_compositing: false,
    light_background_contrast_alpha: '0',
    input_sampling_rate: '0',
    webgpu_prefer_hdr: false
  };

  function keyFor(control) {
    var match = control.name && control.name.match(/\[([^\]]+)\]$/);
    return match ? match[1] : '';
  }

  function syncOutput(control) {
    if (control.type !== 'range') {
      return;
    }

    var output = control.parentElement && control.parentElement.querySelector('output');
    if (output) {
      output.value = control.value;
      output.textContent = control.value;
    }
  }

  form.querySelectorAll('input[type="range"]').forEach(function (control) {
    control.addEventListener('input', function () {
      syncOutput(control);
    });
  });

  form.querySelectorAll('.halopress-fx-advanced-control input, .halopress-fx-advanced-control select').forEach(function (control) {
    control.addEventListener('change', function () {
      var preset = form.querySelector('[name$="[quality_preset]"]');
      if (preset) {
        preset.value = 'custom';
      }
    });
  });

  var reset = document.getElementById('halopress-fx-reset');
  if (reset) {
    reset.addEventListener('click', function () {
      if (!window.confirm('确定恢复 HaloPress-FX 的默认设置吗？保存后才会生效。')) {
        return;
      }

      form.querySelectorAll('input, select').forEach(function (control) {
        var key = keyFor(control);
        if (!Object.prototype.hasOwnProperty.call(defaults, key)) {
          return;
        }

        if (control.type === 'checkbox') {
          control.checked = Boolean(defaults[key]);
        } else {
          control.value = defaults[key];
          syncOutput(control);
        }
      });
    });
  }
})();
