<template>
  <div class="profile-page">

    <section class="profile-posts">
<!--      <article v-for="post in posts" :key="post.id" class="post-card">-->
<!--        <h3 class="post-card__title">{{ post.title }}</h3>-->
<!--        <p v-if="post.comment" class="post-card__comment">{{ post.comment }}</p>-->
<!--        <p class="post-card__content">{{ post.content }}</p>-->
<!--        <time class="post-card__date">{{ formatDate(post.created_at) }}</time>-->
<!--      </article>-->

      <p v-if="!loading && posts.length === 0" class="profile-posts__empty">
        No posts yet
      </p>

      <div v-if="error" class="profile-posts__error">
        Failed to load posts, ping @qwrttqr
        <button @click="retry">Retry</button>
      </div>

      <div ref="sentinel" class="profile-posts__sentinel" />

      <div v-if="loading" class="profile-posts__loading">Loading...</div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import http from '@/plugins/http'

interface UserPostEntity {
  id: number
  content_type: string
  title: string
  comment: string
  content: string
  created_at: string
}

interface ResponseUserPosts {
  posts: UserPostEntity[]
  total: number
}

const PER_PAGE = 20

const posts = ref<UserPostEntity[]>([])
const page = ref(0)
const total = ref(0)
const loading = ref(false)
const error = ref(false)

const hasMore = computed(() => posts.value.length < total.value)

async function loadMore() {
  if (loading.value || !hasMore.value) return

  loading.value = true
  error.value = false

  try {
    const { data } = await http.get<ResponseUserPosts>('/posts/get_user_posts', {
      params: { page: page.value, per_page: PER_PAGE },
    })
    posts.value.push(...data.posts)
    total.value = data.total
    page.value += 1
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

function retry() {
  loadMore()
}

// function formatDate(isoString: string) {
//   return new Date(isoString).toLocaleDateString(undefined, {
//     day: 'numeric',
//     month: 'short',
//     year: 'numeric',
//   })
// }

const sentinel = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

onMounted(() => {
  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0]?.isIntersecting) {
        loadMore()
      }
    },
    { rootMargin: '200px' },
  )
  if (sentinel.value) observer.observe(sentinel.value)

  loadMore()
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<style scoped lang="scss">
.profile-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
</style>
