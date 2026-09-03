<template>
  <span
    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium leading-none whitespace-nowrap"
    :class="toneClasses[tone] ?? toneClasses.neutral"
  >
    <span class="h-1.5 w-1.5 rounded-full" :class="dotClasses[tone] ?? dotClasses.neutral" aria-hidden="true" />
    <slot />
  </span>
</template>

<script setup>
defineProps({
  tone: {
    type: String,
    default: 'neutral', // success | warning | danger | neutral
  },
});

const toneClasses = {
  success: 'bg-success text-success-foreground',
  warning: 'bg-warning text-warning-foreground',
  danger: 'bg-danger text-danger-foreground',
  neutral: 'bg-neutral text-neutral-foreground',
};

const dotClasses = {
  success: 'bg-[#15803d]',
  warning: 'bg-[#a16207]',
  danger: 'bg-[#b91c1c]',
  neutral: 'bg-[#6b7280]',
};
</script>
