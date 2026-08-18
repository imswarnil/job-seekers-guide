<script setup lang="ts">
/**
 * The reading layout: nothing.
 *
 * No header, no footer, no ad rail. A book that arrives inside the site's
 * furniture is a web page with a page-turn animation on it — the point of
 * opening a book is that everything else goes away. The way back out is a
 * control the reader owns, inside the book itself.
 */
</script>

<template>
  <div class="reader">
    <slot />
  </div>
</template>

<style scoped>
/* `dvh`, not `vh`: on a phone the URL bar collapses as you scroll and a `vh`
   book is cropped by exactly that much for the whole session. */
.reader {
  height: 100dvh;
  overflow: hidden;
  background: var(--ui-bg);
}
</style>
