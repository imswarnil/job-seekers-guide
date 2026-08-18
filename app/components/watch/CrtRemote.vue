<script setup lang="ts">
/**
 * The remote.
 *
 * It exists because the television does. A set you switch on and then drive
 * from a row of web buttons underneath breaks the object the moment you touch
 * it — the controls have to live on something you would have been holding.
 *
 * Everything here is a real button with a real label underneath the styling, so
 * the whole thing still works from a keyboard and reads correctly to a screen
 * reader. It is a skin, not a replacement for controls.
 */
defineProps<{
  episode: number
  total: number
  on: boolean
  playing: boolean
  muted: boolean
  canPrevious: boolean
  canNext: boolean
}>()

const emit = defineEmits<{
  power: []
  previous: []
  next: []
  toggleplay: []
  togglemute: []
  skip: [seconds: number]
}>()
</script>

<template>
  <div class="remote">
    <div class="remote__top">
      <span class="remote__ir" />
      <span class="remote__name">JSG</span>
    </div>

    <button
      type="button"
      class="remote__power"
      :data-on="on ? '' : undefined"
      :aria-pressed="on"
      @click="emit('power')"
    >
      <UIcon
        name="i-lucide-power"
        class="size-4"
      />
      <span class="sr-only">{{ on ? 'Switch the television off' : 'Switch the television on' }}</span>
    </button>

    <!-- The channel readout, in the seven-segment green every set had. -->
    <div class="remote__display">
      <span class="remote__ch">CH</span>
      <span class="remote__num">{{ String(episode).padStart(2, '0') }}</span>
      <span class="remote__of">/ {{ String(total).padStart(2, '0') }}</span>
    </div>

    <div class="remote__pad">
      <button
        type="button"
        class="remote__key remote__key--ch"
        :disabled="!canPrevious"
        @click="emit('previous')"
      >
        <UIcon
          name="i-lucide-chevron-up"
          class="size-4"
        />
        <span class="remote__key-label">CH</span>
      </button>

      <button
        type="button"
        class="remote__key"
        :disabled="!on"
        @click="emit('skip', -10)"
      >
        <UIcon
          name="i-lucide-rewind"
          class="size-4"
        />
        <span class="sr-only">Back ten seconds</span>
      </button>

      <button
        type="button"
        class="remote__key remote__key--play"
        :disabled="!on"
        @click="emit('toggleplay')"
      >
        <UIcon
          :name="playing ? 'i-lucide-pause' : 'i-lucide-play'"
          class="size-4"
        />
        <span class="sr-only">{{ playing ? 'Pause' : 'Play' }}</span>
      </button>

      <button
        type="button"
        class="remote__key"
        :disabled="!on"
        @click="emit('skip', 10)"
      >
        <UIcon
          name="i-lucide-fast-forward"
          class="size-4"
        />
        <span class="sr-only">Forward ten seconds</span>
      </button>

      <button
        type="button"
        class="remote__key remote__key--ch"
        :disabled="!canNext"
        @click="emit('next')"
      >
        <UIcon
          name="i-lucide-chevron-down"
          class="size-4"
        />
        <span class="remote__key-label">CH</span>
      </button>
    </div>

    <button
      type="button"
      class="remote__mute"
      :disabled="!on"
      @click="emit('togglemute')"
    >
      <UIcon
        :name="muted ? 'i-lucide-volume-x' : 'i-lucide-volume-2'"
        class="size-3.5"
      />
      {{ muted ? 'Muted' : 'Sound' }}
    </button>
  </div>
</template>

<style scoped>
.remote {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.7rem;
  width: 100%;
  max-width: 12rem;
  padding: 0.9rem 0.85rem 1.1rem;
  border-radius: 1.15rem;
  background: linear-gradient(165deg, #3a3a44, #212128 45%, #16161b);
  box-shadow:
    inset 0 1px 0 rgb(255 255 255 / 0.14),
    inset 0 -2px 6px rgb(0 0 0 / 0.5),
    0 14px 30px rgb(6 8 24 / 0.3);
}

.remote__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.remote__ir {
  width: 1.4rem;
  height: 0.28rem;
  border-radius: 999px;
  background: #6b1414;
  box-shadow: inset 0 0 3px rgb(0 0 0 / 0.7);
}

.remote__name {
  font-family: var(--font-mono);
  font-size: 0.5rem;
  letter-spacing: 0.2em;
  color: rgb(255 255 255 / 0.3);
}

.remote__power {
  display: grid;
  place-items: center;
  width: 2.1rem;
  height: 2.1rem;
  border-radius: 999px;
  background: radial-gradient(circle at 34% 28%, #7a2b2b, #3a1414 72%);
  color: rgb(255 255 255 / 0.6);
  box-shadow: inset 0 -2px 3px rgb(0 0 0 / 0.6), 0 1px 0 rgb(255 255 255 / 0.08);
  transition: color 200ms ease, box-shadow 200ms ease;
}

.remote__power[data-on] {
  color: #ffdada;
  box-shadow:
    inset 0 -2px 3px rgb(0 0 0 / 0.6),
    0 0 12px rgb(255 77 77 / 0.55);
}

.remote__display {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 0.3rem;
  width: 100%;
  padding: 0.3rem 0.5rem;
  border-radius: 0.35rem;
  background: #0b1410;
  box-shadow: inset 0 1px 4px rgb(0 0 0 / 0.8);
  font-family: var(--font-mono);
  color: #6dffb0;
  text-shadow: 0 0 8px rgb(109 255 176 / 0.5);
}

.remote__ch {
  font-size: 0.5rem;
  letter-spacing: 0.16em;
  opacity: 0.7;
}

.remote__num {
  font-size: 1.05rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.remote__of {
  font-size: 0.5rem;
  opacity: 0.55;
}

.remote__pad {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.4rem;
  width: 100%;
}

/* The two channel keys span the row above and below the transport keys, which
   is where a thumb expects them. */
.remote__key--ch {
  grid-column: 1 / -1;
}

.remote__key {
  position: relative;
  display: grid;
  place-items: center;
  height: 2rem;
  border-radius: 0.45rem;
  background: linear-gradient(#4b4b56, #2c2c34);
  color: rgb(255 255 255 / 0.72);
  box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.12), 0 1px 2px rgb(0 0 0 / 0.4);
  transition: transform 90ms ease, background-color 160ms ease;
}

.remote__key:hover:not(:disabled) {
  background: linear-gradient(#575764, #33333c);
}

.remote__key:active:not(:disabled) {
  transform: translateY(1px);
}

.remote__key:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.remote__key--play {
  background: linear-gradient(#5b50dc, #3a32a0);
  color: #fff;
}

.remote__key--play:hover:not(:disabled) {
  background: linear-gradient(#6a5fe6, #443ab4);
}

.remote__key-label {
  position: absolute;
  right: 0.5rem;
  font-family: var(--font-mono);
  font-size: 0.5rem;
  letter-spacing: 0.14em;
  opacity: 0.55;
}

.remote__mute {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  background: rgb(255 255 255 / 0.07);
  color: rgb(255 255 255 / 0.66);
  font-size: 0.6875rem;
  transition: background-color 160ms ease;
}

.remote__mute:hover:not(:disabled) {
  background: rgb(255 255 255 / 0.13);
}

.remote__mute:disabled {
  opacity: 0.35;
}

/* On a phone the remote lies flat under the set as a single row of keys —
   a tall handset beside a full-width television does not fit anywhere. */
@media (max-width: 1023px) {
  .remote {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    max-width: none;
    border-radius: 999px;
    padding: 0.6rem 0.9rem;
  }

  .remote__top {
    display: none;
  }

  .remote__pad {
    display: flex;
    gap: 0.4rem;
  }

  .remote__key--ch {
    width: 3rem;
  }

  .remote__key {
    width: 2.4rem;
  }
}
</style>
