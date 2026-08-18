/**
 * The sound a page makes.
 *
 * Synthesised rather than shipped. A page turn is a burst of broadband noise
 * that loses its top end as the sheet settles — bandpassed white noise with a
 * falling centre frequency is almost exactly that, and it costs nothing to
 * download, never 404s, and can be varied per turn so twenty pages do not sound
 * like the same sample twenty times.
 *
 * Browsers refuse to start an AudioContext without a gesture, so the context is
 * created lazily on the first turn — which is always a click, a key or a drag.
 */

type Turn = 'forward' | 'back'

export function useBookSound() {
  // Persisted, because a reader who turns sound off wants it off tomorrow too.
  const enabled = useLocalStorage('guide:story:sound:v1', true)

  let ctx: AudioContext | undefined
  let noise: AudioBuffer | undefined
  // Varies the timbre per turn so repeated flips do not read as a loop.
  let nth = 0

  function context(): AudioContext | undefined {
    if (ctx) {
      return ctx
    }
    if (typeof window === 'undefined') {
      return undefined
    }

    const Ctor = window.AudioContext || (window as unknown as { webkitAudioContext?: typeof AudioContext }).webkitAudioContext
    if (!Ctor) {
      return undefined
    }

    ctx = new Ctor()

    const seconds = 1
    const buffer = ctx.createBuffer(1, ctx.sampleRate * seconds, ctx.sampleRate)
    const data = buffer.getChannelData(0)
    for (let i = 0; i < data.length; i++) {
      data[i] = Math.random() * 2 - 1
    }
    noise = buffer

    return ctx
  }

  /** Called on the first gesture — Safari suspends contexts created too early. */
  function unlock() {
    const audio = context()
    if (audio?.state === 'suspended') {
      void audio.resume()
    }
  }

  function burst(audio: AudioContext, at: number, opts: {
    from: number
    to: number
    peak: number
    attack: number
    release: number
    q: number
  }) {
    if (!noise) {
      return
    }

    const source = audio.createBufferSource()
    source.buffer = noise
    source.loop = true
    // Start somewhere random in the noise so successive turns differ.
    const offset = Math.random() * 0.8

    const band = audio.createBiquadFilter()
    band.type = 'bandpass'
    band.Q.value = opts.q
    band.frequency.setValueAtTime(opts.from, at)
    band.frequency.exponentialRampToValueAtTime(opts.to, at + opts.release)

    const gain = audio.createGain()
    gain.gain.setValueAtTime(0.0001, at)
    gain.gain.exponentialRampToValueAtTime(opts.peak, at + opts.attack)
    gain.gain.exponentialRampToValueAtTime(0.0001, at + opts.release)

    source.connect(band).connect(gain).connect(audio.destination)
    source.start(at, offset)
    source.stop(at + opts.release + 0.05)
  }

  /** The low body of the sheet landing, under the rustle. */
  function thump(audio: AudioContext, at: number, hz: number, peak: number) {
    const osc = audio.createOscillator()
    osc.type = 'sine'
    osc.frequency.setValueAtTime(hz, at)
    osc.frequency.exponentialRampToValueAtTime(hz * 0.55, at + 0.12)

    const gain = audio.createGain()
    gain.gain.setValueAtTime(0.0001, at)
    gain.gain.exponentialRampToValueAtTime(peak, at + 0.008)
    gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.16)

    osc.connect(gain).connect(audio.destination)
    osc.start(at)
    osc.stop(at + 0.2)
  }

  /**
   * A turn is two sounds: the sheet leaving the stack, and — a beat later, once
   * it has fallen — the softer sound of it landing. One burst on its own reads
   * as static; the pair reads as paper.
   */
  function turn(direction: Turn = 'forward') {
    if (!enabled.value) {
      return
    }

    const audio = context()
    if (!audio || !noise) {
      return
    }
    if (audio.state === 'suspended') {
      void audio.resume()
    }

    nth += 1
    const drift = 1 + ((nth % 5) - 2) * 0.06
    const now = audio.currentTime + 0.01

    burst(audio, now, {
      from: (direction === 'forward' ? 2600 : 2100) * drift,
      to: 620 * drift,
      peak: 0.09,
      attack: 0.014,
      release: 0.24,
      q: 0.75
    })

    burst(audio, now + 0.17, {
      from: 1500 * drift,
      to: 380 * drift,
      peak: 0.045,
      attack: 0.01,
      release: 0.17,
      q: 0.9
    })

    thump(audio, now + 0.2, 108 * drift, 0.05)
  }

  /** The cover. Heavier, slower, with the board landing at the end of it. */
  function open() {
    if (!enabled.value) {
      return
    }

    const audio = context()
    if (!audio || !noise) {
      return
    }
    if (audio.state === 'suspended') {
      void audio.resume()
    }

    const now = audio.currentTime + 0.01

    burst(audio, now, { from: 1400, to: 260, peak: 0.07, attack: 0.06, release: 0.55, q: 0.5 })
    burst(audio, now + 0.5, { from: 900, to: 300, peak: 0.06, attack: 0.01, release: 0.22, q: 0.8 })
    thump(audio, now + 0.52, 78, 0.09)
  }

  function close() {
    if (!enabled.value) {
      return
    }

    const audio = context()
    if (!audio || !noise) {
      return
    }

    const now = audio.currentTime + 0.01
    burst(audio, now, { from: 1800, to: 240, peak: 0.07, attack: 0.02, release: 0.3, q: 0.6 })
    thump(audio, now + 0.24, 68, 0.11)
  }

  return { enabled, unlock, turn, open, close }
}
