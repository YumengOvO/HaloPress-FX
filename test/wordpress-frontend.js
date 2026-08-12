import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(
  new URL('../wordpress/halopress-fx/assets/js/halopress-fx.js', import.meta.url),
  'utf8',
);

function runScenario({ touchPrimary = false, mobileEnabled = true } = {})
{
  let constructedOptions = null;

  class MockBAClickFX
  {
    constructor(options)
    {
      constructedOptions = options;
    }

    destroy()
    {
      // The lifecycle is exercised by the pagehide handler in browsers.
    }
  }

  const windowListeners = new Map();
  const window =
  {
    HaloPressFXSettings:
    {
      clickEnabled: true,
      trailEnabled: true,
      trailAlways: true,
      mobileEnabled,
      themeColor: '#4ca7ff',
      scale: 1,
      opacity: 1,
      clickTimeScale: 1,
      trailTimeScale: 1,
      qualityPreset: 'custom',
      effectBackend: 'webgl2',
      bloomBackend: 'webgl2',
      renderingMode: 'enhanced',
      maxDpr: 1,
      themeColorMode: 'hue-only',
      outputCompositing: 'browser-overlay',
      overlayAlphaPolicy: 'coverage',
      overlayColorCompensation: 'none',
      hostCompositing: 'source-over',
      isolatedCompositing: false,
      lightBackgroundContrastAlpha: 0,
      inputSamplingRate: 0,
      webgpuPreferHdr: false,
    },
    BAClickFX: { BAClickFX: MockBAClickFX },
    matchMedia(query)
    {
      return {
        matches: touchPrimary &&
          (query === '(pointer: coarse)' || query === '(hover: none)'),
      };
    },
    addEventListener(type, listener)
    {
      windowListeners.set(type, listener);
    },
    console,
  };
  const document =
  {
    readyState: 'complete',
    addEventListener() {},
  };

  vm.runInNewContext(source, { window, document, Boolean, Number });
  return { constructedOptions, window, windowListeners };
}

const desktop = runScenario();
assert.ok(desktop.constructedOptions, 'desktop should initialize the effect');
assert.equal(desktop.constructedOptions.trailAlways, true);
assert.equal(desktop.constructedOptions.touchAction, 'auto');
assert.equal(desktop.constructedOptions.outputCompositing, 'browser-overlay');

const touch = runScenario({ touchPrimary: true });
assert.ok(touch.constructedOptions, 'enabled mobile should initialize the effect');
assert.equal(touch.constructedOptions.trailAlways, false);
assert.equal(touch.constructedOptions.touchAction, 'auto');

const mobileDisabled = runScenario({
  touchPrimary: true,
  mobileEnabled: false,
});
assert.equal(
  mobileDisabled.constructedOptions,
  null,
  'disabled mobile mode must not create the effect',
);

console.log('WordPress frontend behavior verified.');
