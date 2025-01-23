<template>
  <div class="comments-thread">
    <template v-for="comment in comments" :key="'comment-' + comment.id">
      <div :id="'topic-comment-' + comment.id" :class="getCommentClass(comment)">
        <template v-if="comment.active || isAdmin">
          <div class="comment-header">
            <div v-if="comment.user" class="comment-user">
              <UserAvatar :user="comment.user" class="me-1" />
              <BLink v-if="isAdmin && comment.user_id !== user?.id" v-bind="getUserLinkAttrs(comment)">
                {{ comment.user.fullname }}
              </BLink>
              <span v-else v-bind="getUserAttrs(comment)">{{ comment.user.fullname }}</span>
            </div>
            <div v-if="comment.created_at" class="comment-date">
              <BLink :href="getCommentLink(comment)" @click="(e) => (onLink ? onLink(e, comment) : {})">
                {{ formatDate(comment.created_at) }}
              </BLink>
            </div>
          </div>
          <div v-if="record.id !== comment.id" class="comment-text">
            <EditorContent :content="comment.content" type="comment" />
          </div>
        </template>
        <div v-else class="comment-text" v-text="$t('components.comments.info.deleted')" />

        <div v-if="user && record.id !== comment.id" class="comment-footer">
          <div v-if="comment.active">
            <UserReactions :item="comment" small>
              <template #default="{selected, total}">
                <BButton :variant="btnVariant">
                  <VespFa :icon="[selected ? 'fas' : 'far', 'face-smile']" fixed /> {{ total }}
                </BButton>
              </template>
            </UserReactions>

            <BButton v-if="canReply && onReply && canReply(comment)" :variant="btnVariant" @click="onReply(comment)">
              <VespFa icon="reply" class="fa-fw" />
              <span class="action">{{ $t('actions.reply') }}</span>
            </BButton>
            <BButton v-if="canEdit && onEdit && canEdit(comment)" :variant="btnVariant" @click="onEdit(comment)">
              <VespFa icon="edit" class="fa-fw" />
              <span class="action">{{ $t('actions.edit') }}</span>
            </BButton>
          </div>
          <div v-if="canDelete && canDelete(comment)" class="ms-auto">
            <BButton
              v-if="onDelete"
              :variant="btnVariant"
              :class="comment.active ? 'text-danger' : 'text-success'"
              @click="onDelete(comment)"
            >
              <VespFa :icon="comment.active ? 'trash' : 'undo'" class="fa-fw" />
              <span class="action">{{ $t('actions.' + (comment.active ? 'delete' : 'restore')) }}</span>
            </BButton>
            <BButton v-if="onDestroy" :variant="btnVariant" class="text-danger" @click="onDestroy(comment)">
              <VespFa icon="times" class="fa-fw" />
              <span class="action">{{ $t('actions.destroy') }}</span>
            </BButton>
          </div>
        </div>
      </div>

      <slot name="form" v-bind="{comment}" />

      <CommentsThread
        v-if="comment.children?.length"
        :key="'children-' + comment.id"
        v-model="record"
        :comments="comment.children"
        :topic="topic"
      >
        <template #form="slotProps">
          <slot name="form" v-bind="slotProps" />
        </template>
      </CommentsThread>
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

function getUserLinkAttrs(comment: VespComment) {
  return {
    ...getUserAttrs(comment),
    target: '_blank',
    href: router.resolve({name: 'admin-users-id-edit', params: {id: comment.user_id}}).href,
  }
}

function getUserAttrs(comment: VespComment) {
  const attrs: Record<string, any> = {}
  const color = comment.user?.role?.color || comment.user?.subscription?.level?.color
  if (color) {
    attrs.style = {color}
  }
  const title = [comment.user?.role?.title]
  if (comment.user?.subscription?.level?.title) {
    title.push(comment.user.subscription.level.title)
  }
  if (title.length) {
    attrs.title = title.join(', ')
  }

  return attrs
}

function getCommentClass(comment: VespComment) {
  return {
    comment: true,
    deleted: !comment.active,
    blocked: comment.user?.blocked,
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
