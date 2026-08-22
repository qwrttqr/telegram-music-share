<template>
  <div class="profile-page">
    <div class="profile-page__header">
      <h3>
        Your posts
      </h3>
      <CommonButton class="profile-page__header__reload" variant="circle" type="button"
                    @click="retry">
        <span class="material-icons">refresh</span>
      </CommonButton>
    </div>
    <section class="profile-posts">
      <PostCard
        v-for="post in myPosts"
        :key="post.id"
        :post="post"
        @onDeleted="removeFromFeed"
      />

      <p v-if="!loading && !myPosts.length && !error" class="profile-posts__empty">
        No posts yet
      </p>

      <div v-if="error" class="profile-posts__error">
        Failed to load posts, ping @qwrttqr
        <CommonButton variant="secondary" @click="retry">Retry</CommonButton>
      </div>

      <div ref="sentinel" class="profile-posts__sentinel"/>

      <div v-if="loading" class="profile-posts__loading">
        <CommonSpinner/>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import {ref, computed, onMounted, onUnmounted} from 'vue'
import http from '@/plugins/http'
import PostCard from '@/components/posts/PostCard.vue'
import CommonSpinner from '@/components/common/CommonSpinner.vue'
import CommonButton from '@/components/common/CommonButton.vue'
import type {MyPost} from '@/types/post.ts'

interface ResponseUserPosts {
  posts: MyPost[]
  total: number
}

const PER_PAGE = 20

const myPosts = ref<MyPost[]>([])
const page = ref(0)
const total = ref(0)
const loading = ref(false)
const error = ref(false)

const hasMore = computed(() => myPosts.value.length < total.value || page.value === 1)

async function loadMore() {
  if (loading.value || (!hasMore.value && page.value > 1)) return

  loading.value = true
  error.value = false

  try {
    const {data} = await http.get<ResponseUserPosts>('/posts/get_my_posts', {
      params: {page: page.value, per_page: PER_PAGE},
    })
    myPosts.value.push(...data.posts)
    total.value = data.total
    page.value += 1
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

function removeFromFeed(id: number) {
  myPosts.value = myPosts.value.filter(post => post.id !== id)
}
function retry() {
  loadMore()
}

const sentinel = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

onMounted(() => {
  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0]?.isIntersecting) {
        loadMore()
      }
    },
    {rootMargin: '200px'},
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
  padding: 16px;

  &__header {
    display: flex;
    gap: 10px;
    align-items: center;
    color: var(--tg-theme-text-color, #fff);

    &__reload {
      border: none;
      width: 30px;
      height: 30px;
      background: transparent;
    }

    &__reload:hover {
      background: rgba(255, 255, 255, 0.1);
    }
  }
}

.profile-posts {
  display: flex;
  flex-direction: column;
  gap: 12px;

  &__empty {
    text-align: center;
    color: var(--tg-theme-hint-color, #999);
    padding: 24px 0;
    font-size: 14px;
  }

  &__error {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 24px 0;
    color: #ff6b6b;
    font-size: 13px;
    text-align: center;
  }

  &__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
  }

  &__sentinel {
    height: 1px;
  }
}
</style>
