<script setup lang="ts">
import type { Subject } from '~/utils/path'

const props = defineProps<{
  subject: Subject
  /** Position along the whole path. Drawn large, behind the card. */
  index?: number
}>()

const { subjectProgress } = useProgress()

const progress = computed(() => subjectProgress(props.subject))

const meta = computed(() => [
  props.subject.duration,
  props.subject.lessons.length === 1 ? '1 lesson' : `${props.subject.lessons.length} lessons`
].filter(Boolean))

const number = computed(() => props.index === undefined ? '' : String(props.index + 1))
</script>

<template>
  <div class="subject">
    <!-- The number lives outside the card and is half hidden behind it. The
         order of the path is the entire product; as a small badge inside the
         card it was something nobody read. -->
    <span
      v-if="number"
      class="subject__number"
      aria-hidden="true"
    >{{ number }}</span>

    <NuxtLink
      :to="subject.path"
      class="subject__card"
    >
      <!-- A picture, not an icon in a corner. Sixteen by nine with the mark
           centred and large, so a row of these reads as artwork the way a row
           of episodes does. -->
      <SubjectThumb
        :subject="subject"
        size="card"
        class="subject__thumb"
      />

      <div class="subject__inner">
        <!-- One mark per card. The technology strip used to sit here as well,
             which meant every card carried the same idea twice — the big glyph
             in the thumbnail already says which subject this is. -->
        <h3 class="font-display font-semibold text-highlighted text-balance">
          {{ subject.title }}
        </h3>

        <p class="text-sm text-muted mt-1.5 line-clamp-3">
          {{ subject.description }}
        </p>

        <div class="mt-auto pt-4">
          <div class="flex items-center gap-2 flex-wrap text-xs text-dimmed">
            <UBadge
              v-if="subject.code"
              :label="subject.code"
              color="neutral"
              variant="subtle"
              size="sm"
            />
            <span>{{ meta.join(' · ') }}</span>
          </div>

          <ClientOnly>
            <div
              v-if="progress.started"
              class="mt-3"
            >
              <UProgress
                :model-value="progress.percent"
                size="xs"
                :color="progress.finished ? 'success' : 'primary'"
              />
              <p class="text-xs text-muted mt-1.5">
                {{ progress.finished ? 'Finished' : `${progress.percent}% complete` }}
              </p>
            </div>
          </ClientOnly>
        </div>
      </div>
    </NuxtLink>
  </div>
</template>

<style scoped>
.subject {
  position: relative;
  padding-left: 1.75rem;
}

@media (min-width: 640px) {
  .subject {
    padding-left: 2.25rem;
  }
}

.subject__number {
  position: absolute;
  left: 0;
  bottom: -0.35rem;
  z-index: 0;
  font-family: var(--font-display);
  font-size: 5.5rem;
  font-weight: 800;
  line-height: 0.8;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.06em;
  color: transparent;
  -webkit-text-stroke: 2px var(--ui-border-accented);
  transition: -webkit-text-stroke-color var(--dgm-t-base) var(--dgm-ease);
  pointer-events: none;
  user-select: none;
}

@media (min-width: 640px) {
  .subject__number {
    font-size: 7rem;
    -webkit-text-stroke-width: 2.5px;
  }
}

.subject:hover .subject__number {
  -webkit-text-stroke-color: var(--ui-primary);
}

.subject__card {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  /* The thumbnail runs to the card's edges, so the padding moved inside. */
  overflow: hidden;
  border: 1px solid var(--ui-border);
  border-radius: var(--radius-lg);
  background: var(--ui-bg);
  transition:
    border-color var(--dgm-t-fast) var(--dgm-ease),
    transform var(--dgm-t-fast) var(--dgm-ease),
    box-shadow var(--dgm-t-fast) var(--dgm-ease);
}

.subject__inner {
  display: flex;
  flex-direction: column;
  flex: 1;
  padding: 1.15rem 1.25rem 1.25rem;
}

.subject__card:hover {
  border-color: var(--ui-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

/* The mark leans in a little when the card is hovered. Small, and it is what
   makes a still row feel like it is made of things rather than of boxes. */
.subject__card:hover .subject__thumb :deep(.sthumb__mark) {
  transform: scale(1.08);
}
</style>
