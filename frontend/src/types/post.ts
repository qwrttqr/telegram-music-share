type TrackVendor = 'spotify' | 'yandex' | 'vk'

export interface CreatePostRequest {
  vendor: TrackVendor
  link: string
  title: string
  comment: string
}

export interface BasePost {
  id: number
  vendor: TrackVendor
  title: string
  comment: string
  link: string
  created_at: string
}


export interface Post extends BasePost {
  author: {
    id: number
    telegram_id: number
    tg_username: string | null
    photo_url: string | null
    first_name: string | null
    last_name: string | null
  }
  seen: boolean
}

export type MyPost = BasePost

export interface CreatePostResponse {
  success: boolean
  post: Post
}
