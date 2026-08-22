<template>
  <div class="posts-page">
    <section class="posts-header">
      <div class="posts-header__title">
        Posts
      </div>

      <CommonButton
        :variant="'circle'"
        :type="'button'"
        @click="showCreateModal = true"
      >
        <span class="material-icons posts-header__create__material-icons">add</span>
      </CommonButton>
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
    </section>

    <CreatePostModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @created="handlePostCreated"
    />
  </div>
</template>

<script setup lang="ts">
import {onMounted, ref} from 'vue'
import http from '@/plugins/http'
import CommonSpinner from '@/components/common/CommonSpinner.vue'
import PostCard from '@/components/posts/PostCard.vue'
import CreatePostModal from '@/components/posts/CreatePostModal.vue'
import type {Post} from "@/types/post.ts";
import CommonButton from "@/components/common/CommonButton.vue";

const posts = ref<Post[]>([])
const postsLoading = ref(false)
const showCreateModal = ref(false)

async function loadPosts() {
  postsLoading.value = true

  try {
    const {data} = await http.get<{posts: Post[]}>(
      '/posts/get_posts'
    )

    posts.value = data.posts
  } finally {
    postsLoading.value = false
  }
}

function handlePostCreated() {
  showCreateModal.value = false
  loadPosts()
  // Todo: add feed returning on backend so it will add it into posts instead of refetching from backend
}

onMounted(loadPosts)
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
}
</style>
