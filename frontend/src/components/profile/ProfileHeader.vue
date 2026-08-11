<template>
  <header class="profile-header">
    <div class="profile-container">
      <img
        v-if="photoUrl"
        :src="photoUrl"
        :alt="displayName"
        class="profile-header__avatar"
      />
      <div v-else class="profile-header__avatar profile-header__avatar--placeholder">
        {{ displayName.charAt(0).toUpperCase() }}
      </div>
      <div class="profile-header__info">
        <span class="profile-header__name">{{ displayName }}</span>
        <span v-if="username" class="profile-header__username">
          @{{ username }}
        </span>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
defineProps<{
  photoUrl?: string | null
  displayName: string
  username?: string | null
}>()
</script>

<style scoped lang="scss">
.profile-header {
  display: flex;
  padding: 16px;
  justify-content: space-around;

  &__avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;

    &--placeholder {
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--tg-theme-button-color, #999);
      color: var(--tg-theme-button-text-color, #fff);
      font-weight: 600;
    }
  }

  &__info {
    display: flex;
    flex-direction: column;
  }

  &__name {
    color: var(--tg-theme-button-text-color, #fff);
    font-weight: 600;
    font-size: 16px;
  }

  &__username {
    font-size: 13px;
    color: var(--tg-theme-text-color, #999);
  }
}

.profile-container {
  display: flex;
  align-items: center;
  gap: 20px;
}
</style>
