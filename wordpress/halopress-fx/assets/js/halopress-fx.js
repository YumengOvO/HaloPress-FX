(function () {
  'use strict';

  var instance = null;

  function primaryPointerIsTouch() {
    return Boolean(
      window.matchMedia &&
      window.matchMedia('(pointer: coarse)').matches &&
      window.matchMedia('(hover: none)').matches
    );
  }

  function applyQualityPreset(options, preset) {
    if (preset === 'performance') {
      options.effectBackend = 'canvas2d';
      options.bloomBackend = 'native';
      options.maxDpr = 1;
    } else if (preset === 'balanced') {
      options.effectBackend = 'webgl2';
      options.bloomBackend = 'webgl2';
      options.maxDpr = 1;
    } else if (preset === 'high') {
      options.effectBackend = 'webgl2';
      options.bloomBackend = 'webgl2';
      options.maxDpr = Math.max(2, Number(options.maxDpr) || 1);
    }

    return options;
  }

  function createOptions(settings, touchPrimary) {
    var options = {
      clickEnabled: Boolean(settings.clickEnabled),
      trailEnabled: Boolean(settings.trailEnabled),
      trailAlways: Boolean(settings.trailAlways) && !touchPrimary,
      themeColor: settings.themeColor,
      scale: Number(settings.scale),
      opacity: Number(settings.opacity),
      clickTimeScale: Number(settings.clickTimeScale),
      trailTimeScale: Number(settings.trailTimeScale),
      effectBackend: settings.effectBackend,
      bloomBackend: settings.bloomBackend,
      renderingMode: settings.renderingMode,
      maxDpr: Number(settings.maxDpr),
      themeColorMode: settings.themeColorMode,
      outputCompositing: settings.outputCompositing,
      overlayAlphaPolicy: settings.overlayAlphaPolicy,
      overlayColorCompensation: settings.overlayColorCompensation,
      hostCompositing: settings.hostCompositing,
      hostCompositingSurface: 'dom-backdrop',
      isolatedCompositing: Boolean(settings.isolatedCompositing),
      lightBackgroundContrastAlpha: Number(settings.lightBackgroundContrastAlpha),
      inputSamplingRate: Number(settings.inputSamplingRate),
      webgpuPreferHdr: Boolean(settings.webgpuPreferHdr),
      inputSource: 'dom',
      touchAction: 'auto'
    };

    return applyQualityPreset(options, settings.qualityPreset);
  }

  function start() {
    if (instance) {
      return;
    }

    var settings = window.HaloPressFXSettings || {};
    var touchPrimary = primaryPointerIsTouch();
    if (touchPrimary && !settings.mobileEnabled) {
      return;
    }

    var Constructor = window.BAClickFX && window.BAClickFX.BAClickFX;
    if (typeof Constructor !== 'function') {
      return;
    }

    try {
      instance = new Constructor(createOptions(settings, touchPrimary));
      window.HaloPressFX = instance;
    } catch (error) {
      if (window.console && typeof window.console.warn === 'function') {
        window.console.warn('[HaloPress-FX] Animation initialization failed.', error);
      }
    }
  }

  function stop() {
    if (!instance) {
      return;
    }

    instance.destroy();
    instance = null;
    window.HaloPressFX = null;
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }

  window.addEventListener('pagehide', stop);
  window.addEventListener('pageshow', function () {
    if (!instance) {
      start();
    }
  });
})();
