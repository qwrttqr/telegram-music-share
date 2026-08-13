<template>
  <span
    class="spinner"
    :style="{
      width: `${size}px`,
      height: `${size}px`,
      borderWidth: `${thickness}px`,
      borderTopColor: color
    }"
  ></span>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  size?: number
  thickness?: number
  color?: string
}>(), {
  size: 28,
  thickness: 3,
  color: 'var(--tg-theme-button-color, #4a9eff)'
})
</script>

<style scoped lang="scss">
.spinner {
  display: inline-block;
  border-style: solid;
  border-color: rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
