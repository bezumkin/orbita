<template>
  <div class="comments-thread">
    <template v-for="comment in comments" :key="'comment-' + comment.id">
      <div :id="'topic-comment-' + comment.id" :class="getCommentClass(comment)">
        <template v-if="comment.active || isAdmin">
          <div class="comment-header">
            <div v-if="comment.user" class="comment-user">
              <user-avatar :user="comment.user" class="me-1" /> {{ comment.user.fullname }}
            </div>
            <div v-if="comment.created_at" class="comment-date">
              <b-link :href="getCommentLink(comment)" @click="(e) => (onLink ? onLink(e, comment) : {})">
                {{ d(comment.created_at, 'long') }}
              </b-link>
            </div>
          </div>
          <div v-if="record.id !== comment.id" class="comment-text">
            <editor-content :content="comment.content" type="comment" />
          </div>
        </template>
        <div v-else class="comment-text" v-text="$t('components.comments.info.deleted')" />

        <div v-if="user && record.id !== comment.id" class="comment-footer">
          <div v-if="comment.active">
            <b-button v-if="canReply && onReply && canReply(comment)" :variant="btnVariant" @click="onReply(comment)">
              <vesp-fa icon="reply" class="fa-fw" />
              <span class="action">{{ $t('actions.reply') }}</span>
            </b-button>
            <b-button v-if="canEdit && onEdit && canEdit(comment)" :variant="btnVariant" @click="onEdit(comment)">
              <vesp-fa icon="edit" class="fa-fw" />
              <span class="action">{{ $t('actions.edit') }}</span>
            </b-button>
          </div>
          <div v-if="canDelete && canDelete(comment)" class="ms-auto">
            <b-button
              v-if="onDelete"
              :variant="btnVariant"
              :class="comment.active ? 'text-danger' : 'text-success'"
              @click="onDelete(comment)"
            >
              <vesp-fa :icon="comment.active ? 'trash' : 'undo'" class="fa-fw" />
              <span class="action">{{ $t('actions.' + (comment.active ? 'delete' : 'restore')) }}</span>
            </b-button>
            <b-button v-if="onDestroy" :variant="btnVariant" class="text-danger" @click="onDestroy(comment)">
              <vesp-fa icon="times" class="fa-fw" />
              <span class="action">{{ $t('actions.destroy') }}</span>
            </b-button>
          </div>
        </div>
      </div>

      <slot name="form" v-bind="{comment}" />

      <comments-thread
        v-if="comment.children?.length"
        :key="'children-' + comment.id"
        v-model="record"
        :comments="comment.children"
        :topic="topic"
      >
        <template #form="slotProps">
          <slot name="form" v-bind="slotProps" />
        </template>
      </comments-thread>
    </template>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespComment>,
    required: true,
  },
  comments: {
    type: Array as PropType<VespComment[]>,
    required: true,
  },
  topic: {
    type: Object as PropType<VespTopic>,
    default() {
      return {}
    },
  },
})
const emit = defineEmits(['update:modelValue'])
const record = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})
defineSlots<{
  form(props: {comment: VespComment}): any
}>()

const route = useRoute()
const router = useRouter()
const {user} = useAuth()
const {d} = useI18n()
const {$scope, $isMobile} = useNuxtApp()
const isAdmin = computed(() => {
  return $scope('comments/delete')
})
const btnVariant = computed(() => {
  return $isMobile.value ? 'light' : 'link'
})

function getCommentLink(comment: VespComment) {
  return router.resolve({...route, hash: '#comment-' + comment.id}).href
}

function getCommentClass(comment: VespComment) {
  return {
    comment: true,
    deleted: !comment.active,
    author: (comment.active || isAdmin.value) && props.topic.user_id === comment.user_id,
    admin: (comment.active || isAdmin.value) && comment.user?.role_id === 1,
    // paid: Boolean(comment.user?.paid),
    unseen:
      (comment.active || isAdmin.value) &&
      user.value &&
      user.value.id !== comment.user_id &&
      comment.created_at &&
      props.topic.viewed_at &&
      props.topic.viewed_at < comment.created_at,
  }
}

const canReply = inject<(comment: VespComment) => boolean>('canReply')
const onReply = inject<(comment: VespComment) => void>('onReply')
const canEdit = inject<(comment: VespComment) => boolean>('canEdit')
const onEdit = inject<(comment: VespComment) => void>('onEdit')
const canDelete = inject<(comment: VespComment) => boolean>('canDelete')
const onDelete = inject<(comment: VespComment) => void>('onDelete')
const onDestroy = inject<(comment: VespComment) => void>('onDestroy')
const onLink = inject<(e: MouseEvent, comment: VespComment) => void>('onLink')
</script>
