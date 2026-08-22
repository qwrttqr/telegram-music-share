<template>
  <div class="posts-page">
    <section class="posts-header">
      <div class="posts-header__title">Feed</div>
    </section>

    <section class="posts-list">
      <div v-if="postsLoading && !posts.length" class="posts-list__loader">
        <CommonSpinner/>
      </div>

      <p v-else-if="!posts.length" class="posts-list__placeholder">
        No posts yet
      </p>

      <PostCard
        v-for="post in posts"
        :key="post.id"
        :post="post"
      />

      <div v-if="posts.length && hasMore" ref="sentinel" class="posts-list__sentinel">
        <CommonSpinner v-if="postsLoading"/>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import {computed, nextTick, onBeforeUnmount, onMounted, ref} from 'vue'
import http from '@/plugins/http'
import CommonSpinner from '@/components/common/CommonSpinner.vue'
import PostCard from '@/components/posts/PostCard.vue'
import type {Post} from "@/types/post.ts";
const PER_PAGE = 10

const posts = ref<Post[]>([])
const postsLoading = ref(false)
const showCreateModal = ref(false)
const page = ref(0)
const total = ref(0)
const sentinel = ref<HTMLElement | null>(null)

const hasMore = computed(() => posts.value.length < total.value)

let observer: IntersectionObserver | null = null

async function loadPosts(reset = false) {
  if (postsLoading.value) return
  if (reset) {
    page.value = 0
    posts.value = []
    total.value = 0
  } else if (!hasMore.value && page.value > 0) {
    return
  }

  postsLoading.value = true

  try {
    const {data} = await http.get<{posts: Post[]; total: number}>(
      '/posts/get_feed_posts',
      {params: {page: page.value, per_page: PER_PAGE}}
    )

    posts.value = reset ? data.posts : [...posts.value, ...data.posts]
    total.value = data.total
    page.value += 1
  } finally {
    postsLoading.value = false
  }
}

function setupObserver() {
  observer = new IntersectionObserver((entries) => {
    if (entries[0]!.isIntersecting && hasMore.value && !postsLoading.value) {
      loadPosts()
    }
  })

  if (sentinel.value) observer.observe(sentinel.value)
}

onMounted(async () => {
  await loadPosts(true)
  await nextTick()
  setupObserver()
})

onBeforeUnmount(() => {
  observer?.disconnect()
})
</script>

<style scoped lang="scss">
.posts-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 16px;
}

.posts-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  &__title {
    color: var(--tg-theme-text-color, #fff);
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.3px;
  }

  &__actions {
    display: flex;
    gap: 8px;
  }
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: 12px;

  &__loader {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 0;
  }

  &__placeholder {
    margin: 0;
    padding: 48px 16px;
    text-align: center;
    color: var(--tg-theme-hint-color, #999);
    font-size: 14px;
  }

  &__sentinel {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 0;
    min-height: 1px;
  }
}
</style>
