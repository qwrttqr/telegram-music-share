<template>
  <article class="post-card">
    <ProfileHeader
      v-if="isPost(post)"
      :content-justify="'start'"
      :photo-url="post.author.photo_url"
      :display-name="displayName"
      :username="post.author.tg_username"
    />

    <div class="post-card__content" :class="{ 'post-card__content--no-header': !isPost(post) }">
      <h3 class="post-card__title">{{ post.title }}</h3>
      <p class="post-card__comment">{{ post.comment }}</p>

      <div v-if="embedUrl" class="post-card__media">
        <iframe
          :src="embedUrl"
          width="100%"
          height="352"
          allowfullscreen
          allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
          loading="lazy"
        />
      </div>

      <a v-else :href="post.link" target="_blank" rel="noopener" class="post-card__link">
        Open track
      </a>
    </div>

    <div class="post-card__footer">
      <span>{{ formattedDate }}</span>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ProfileHeader from '@/components/profile/ProfileHeader.vue'
import { toSpotifyEmbedUrl } from '@/plugins/useSpotify'
import type { Post, MyPost } from '@/types/post.ts'

const props = defineProps<{
  post: Post | MyPost
}>()

function isPost(post: Post | MyPost): post is Post {
  return 'author' in post
}

const embedUrl = computed(() => {
  if (props.post.vendor !== 'spotify') return null
  return toSpotifyEmbedUrl(props.post.link)
})

const displayName = computed(() => {
  if (!isPost(props.post)) return ''
  const name = [props.post.author.first_name, props.post.author.last_name].filter(Boolean).join(' ')
  return name || props.post.author.tg_username || 'Unknown'
})

const formattedDate = computed(() => new Date(props.post.created_at).toLocaleDateString())
</script>

<style scoped lang="scss">
.post-card {
  padding: 12px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.1);

  &__content {
    margin-top: 12px;

    &--no-header {
      margin-top: 0;
    }
  }

  &__title {
    margin: 0;
    color: var(--tg-theme-text-color, #fff);
    font-size: 16px;
    font-weight: 600;
  }

  &__comment {
    margin: 6px 0 12px;
    color: var(--tg-theme-text-color, #fff);
    font-size: 14px;
  }

  &__media {
    overflow: hidden;
    border-radius: 10px;

    iframe {
      display: block;
      width: 100%;
      border: none;
    }
  }

  &__link {
    display: block;
    padding: 12px;
    background: var(--tg-theme-secondary-bg-color, #f5f5f5);
    color: var(--tg-theme-link-color, #4a9eff);
    text-align: center;
    text-decoration: none;
    font-size: 14px;
  }

  &__footer {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    color: var(--tg-theme-hint-color, #999);
    font-size: 12px;
  }
}
</style>
