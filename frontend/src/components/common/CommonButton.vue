<!-- src/components/common/CommonButton.vue -->
<template>
  <button
    class="common-button"
    :class="[`common-button--${variant}`, { 'common-button--icon': icon }]"
    :type="type"
    :disabled="disabled || loading"
    @click="emit('click', $event)"
  >
    <span v-if="loading" class="common-button__spinner" />
    <span v-else-if="icon" class="material-icons common-button__icon">{{ icon }}</span>
    <slot v-else />
  </button>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary' | 'circle'
    type?: 'button' | 'submit' | 'reset'
    disabled?: boolean
    loading?: boolean
    icon?: string
  }>(),
  {
    variant: 'primary',
    type: 'button',
    disabled: false,
    loading: false,
    icon: undefined,
  }
)

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()
</script>

<style scoped lang="scss">
.common-button {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  font-family: inherit;
  font-size: 15px;
  font-weight: 600;
  transition: transform 0.15s ease, opacity 0.15s ease;

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  &:not(:disabled):hover {
    opacity: 0.9;
  }

  &:not(:disabled):active {
    transform: scale(0.96);
  }

  &--primary {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    background: var(--tg-theme-button-color, #4a9eff);
    color: var(--tg-theme-button-text-color, #fff);
  }

  &--secondary {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    background: var(--tg-theme-secondary-bg-color, #333);
    color: var(--tg-theme-text-color, #fff);
  }

  &--circle {
    width: 40px;
    height: 40px;
    padding: 0;
    border-radius: 50%;
    background: var(--tg-theme-button-color, #4a9eff);
    color: var(--tg-theme-button-text-color, #fff);
  }

  &__icon {
    font-size: 22px;
    line-height: 1;
  }

  &__spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: currentColor;
    border-radius: 50%;
    animation: common-button-spin 0.6s linear infinite;
  }
}

@keyframes common-button-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
