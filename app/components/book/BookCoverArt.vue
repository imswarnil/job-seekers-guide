<script setup lang="ts">
/**
 * The cover image.
 *
 * Drawn rather than photographed, and originally rather than licensed: this is
 * the figure the story is about — a graduate on a small-town road with a file of
 * printed résumés under one arm and a bag over the other shoulder, walking
 * toward something he has not been told the shape of.
 *
 * If a real photograph ever exists, set `story.cover` in `app.config.ts` and
 * `BookCover` uses that instead. Nothing here has to change.
 */
defineProps<{
  /** `back` flips and dims it, so the two boards are the same scene. */
  face?: 'front' | 'back'
}>()
</script>

<template>
  <svg
    class="art"
    :data-face="face || 'front'"
    viewBox="0 0 400 560"
    preserveAspectRatio="xMidYMax slice"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
  >
    <defs>
      <linearGradient
        id="art-sky"
        x1="0"
        y1="0"
        x2="0"
        y2="560"
        gradientUnits="userSpaceOnUse"
      >
        <stop
          offset="0"
          stop-color="#1e1b4b"
        />
        <stop
          offset="0.45"
          stop-color="#272185"
        />
        <stop
          offset="1"
          stop-color="#0f766e"
        />
      </linearGradient>

      <linearGradient
        id="art-road"
        x1="0"
        y1="400"
        x2="0"
        y2="560"
        gradientUnits="userSpaceOnUse"
      >
        <stop
          offset="0"
          stop-color="#14122f"
          stop-opacity="0.15"
        />
        <stop
          offset="1"
          stop-color="#05060f"
          stop-opacity="0.75"
        />
      </linearGradient>

      <linearGradient
        id="art-figure"
        x1="150"
        y1="260"
        x2="270"
        y2="560"
        gradientUnits="userSpaceOnUse"
      >
        <stop
          offset="0"
          stop-color="#0b0a1f"
        />
        <stop
          offset="1"
          stop-color="#05060f"
        />
      </linearGradient>
    </defs>

    <rect
      width="400"
      height="560"
      fill="url(#art-sky)"
    />

    <!-- Trees, as flat masses. A cover is read at thumbnail size; detail here
         would turn to noise before it turned into a tree. -->
    <g opacity="0.5">
      <circle
        cx="62"
        cy="182"
        r="86"
        fill="#0d9488"
        opacity="0.5"
      />
      <circle
        cx="134"
        cy="132"
        r="70"
        fill="#14b8a6"
        opacity="0.35"
      />
      <circle
        cx="330"
        cy="152"
        r="96"
        fill="#0f766e"
        opacity="0.45"
      />
      <circle
        cx="262"
        cy="106"
        r="58"
        fill="#2dd4bf"
        opacity="0.22"
      />
    </g>

    <!-- The light he is walking into. -->
    <ellipse
      cx="212"
      cy="300"
      rx="150"
      ry="120"
      fill="#5eead4"
      opacity="0.13"
    />

    <!-- The road. -->
    <path
      d="M0 560V430C70 412 140 402 212 402C284 402 340 412 400 430V560Z"
      fill="url(#art-road)"
    />
    <path
      d="M212 402C160 448 96 500 40 560"
      stroke="#f4f5ff"
      stroke-opacity="0.07"
      stroke-width="2"
    />
    <path
      d="M212 402C262 448 320 500 372 560"
      stroke="#f4f5ff"
      stroke-opacity="0.07"
      stroke-width="2"
    />

    <!-- The figure, from behind and slightly turned, as a silhouette. A face
         drawn badly is worse than no face, and the story is about anybody who
         has stood on that road — not about one recognisable person. -->
    <g>
      <!-- Shadow -->
      <ellipse
        cx="204"
        cy="552"
        rx="72"
        ry="14"
        fill="#05060f"
        opacity="0.4"
      />

      <!-- Legs -->
      <path
        d="M176 560V470H228V560Z"
        fill="url(#art-figure)"
      />
      <path
        d="M200 470V560"
        stroke="#1e1b4b"
        stroke-width="3"
        opacity="0.6"
      />

      <!-- Torso -->
      <path
        d="M160 478C158 410 166 356 176 330C184 310 224 310 232 330C244 358 250 412 248 478Z"
        fill="url(#art-figure)"
      />

      <!-- The strap and the bag: the two details that say "on the way to an
           interview" rather than "standing in a field". -->
      <path
        d="M186 328L246 452"
        stroke="#0b0a1f"
        stroke-width="9"
        stroke-linecap="round"
      />
      <rect
        x="234"
        y="440"
        width="56"
        height="46"
        rx="7"
        fill="#0b0a1f"
      />
      <rect
        x="234"
        y="452"
        width="56"
        height="6"
        fill="#1e1b4b"
        opacity="0.8"
      />

      <!-- The file of printed résumés. -->
      <g transform="rotate(-9 148 428)">
        <rect
          x="118"
          y="404"
          width="62"
          height="48"
          rx="3"
          fill="#f4f5ff"
          opacity="0.9"
        />
        <rect
          x="122"
          y="399"
          width="62"
          height="48"
          rx="3"
          fill="#fdfcf8"
        />
        <rect
          x="130"
          y="409"
          width="34"
          height="3"
          rx="1.5"
          fill="#a099f5"
        />
        <rect
          x="130"
          y="417"
          width="46"
          height="2.5"
          rx="1.25"
          fill="#c3bffb"
        />
        <rect
          x="130"
          y="424"
          width="40"
          height="2.5"
          rx="1.25"
          fill="#c3bffb"
        />
        <rect
          x="130"
          y="431"
          width="44"
          height="2.5"
          rx="1.25"
          fill="#c3bffb"
        />
      </g>

      <!-- Arm holding the file -->
      <path
        d="M172 340L150 412"
        stroke="#0b0a1f"
        stroke-width="17"
        stroke-linecap="round"
      />

      <!-- Head and hair -->
      <circle
        cx="204"
        cy="296"
        r="30"
        fill="#0b0a1f"
      />
      <path
        d="M204 262C224 262 234 276 233 294C232 306 228 312 224 312C220 300 188 300 184 312C180 312 176 306 175 294C174 276 184 262 204 262Z"
        fill="#05060f"
      />
    </g>

    <!-- Grain. A cover with a perfectly clean gradient reads as a slide. -->
    <rect
      width="400"
      height="560"
      fill="url(#art-sky)"
      opacity="0"
    />
  </svg>
</template>

<style scoped>
.art {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.art[data-face='back'] {
  transform: scaleX(-1);
  opacity: 0.55;
  filter: saturate(0.75);
}
</style>
