<template>
  <div class="column">
    <BOverlay id="topic-comments" ref="tree" :show="pending" opacity="0.5">
      <h4>{{ t('components.comments.title', {total}, total) }}</h4>

      <CommentsThread v-model="commentForm" :comments="commentsTree" :topic="topic" :loading-form="submitting">
        <template #form="{comment}">
          <ClientOnly>
            <CommentsForm
              v-if="replying?.id === comment.id || editing?.id === comment.id"
              v-model="commentForm"
              :topic="topic"
              :loading="submitting"
              :timer="editingTime"
              class="mt-1"
              autofocus
            />
          </ClientOnly>
        </template>
      </CommentsThread>
    </BOverlay>

    <ClientOnly>
      <div v-if="topic.closed && !isAdmin" class="alert alert-info mt-5">
        {{ t('components.comments.info.closed') }}
      </div>
      <template v-else-if="$scope('comments/put')">
        <div v-if="subRequired" class="alert alert-info mt-5">
          {{ t('components.comments.info.no_subscription') }}
        </div>
        <CommentsForm
          v-else-if="!replying && !editing"
          ref="form"
          v-model="commentForm"
          :topic="topic"
          :loading="submitting"
          class="mt-5"
        />
      </template>
      <div v-else class="alert alert-warning mt-5">
        {{ user ? t('components.comments.info.no_scope') : t('components.comments.info.guest') }}
      </div>

      <BModal v-if="isAdmin" :model-value="Boolean(destroying)" centered @hidden="destroying = 0">
        <template #title>
          {{ t('components.comments.destroy.title') }}
        </template>
        <div v-html="t('components.comments.destroy.text')" />
        <template #footer="{hide}">
          <BButton @click="() => hide()">{{ t('actions.cancel') }}</BButton>
          <BButton variant="danger" @click="destroy">{{ t('actions.delete') }}</BButton>
        </template>
      </BModal>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import {differenceInSeconds} from 'date-fns'

const props = defineProps({
  topic: {
    type: Object as PropType<VespTopic>,
    required: true,
  },
})

const {$socket, $scope, $isMobile, $variables} = useNuxtApp()
const {t} = useI18n()
const {user} = useAuth()

const url = 'web/topics/' + props.topic.uuid + '/comments'
const editTime = Number($variables.value.COMMENTS_EDIT_TIME) || 600
const maxLevel: Ref<number> = computed(() => {
  return $isMobile.value ? 1 : Number($variables.value.COMMENTS_MAX_LEVEL) || 5
})
const commentForm: Ref<VespComment> = ref({id: 0, parent_id: 0, content: {}})
const form = ref()
const tree = ref()

const {data, pending} = await useCustomFetch(url)
const comments = computed(() => data.value?.rows || [])
const total = computed(() => comments.value.length)
const commentsTree = computed(() => {
  return comments.value.length ? buildTree(comments.value) : []
})

const isAdmin = computed(() => $scope('comments/delete'))
const isVip = computed(() => $scope('vip'))
// Sub is required for free topics
const subRequired = computed(() => {
  return (
    $variables.value.COMMENTS_REQUIRE_SUBSCRIPTION === '1' &&
    !isAdmin.value &&
    !isVip.value &&
    !props.topic.paid &&
    !user.value?.subscription?.level_id
  )
})
const submitting = ref(false)
const destroying = ref(0)
const replying: Ref<VespComment | undefined> = ref()
const editing: Ref<VespComment | undefined> = ref()
const editingTime: Ref<number | undefined> = ref()
const timer: Ref<NodeJS.Timeout | undefined> = ref()

function buildTree(nodes: VespComment[], depth: number = maxLevel.value) {
  // https://stackoverflow.com/a/74414300/2565349
  const index: Record<number, VespComment> = Object.fromEntries(nodes.map((node) => [node.id, {...node}]))
  // @ts-ignore
  const ancestry = function* (id: number) {
    if (id) {
      yield id
      if (index[id]) {
        yield* ancestry(index[id].parent_id)
      }
    }
  }

  nodes.forEach((node) => {
    const [ancestor] = [...ancestry(node.parent_id)].slice(-depth)
    if (ancestor && index[ancestor]) {
      index[ancestor].children = index[ancestor].children || []
      index[ancestor].children?.push(index[node.id])
    }
  })

  return Object.values(index).filter((node) => !node.parent_id)
}

function getTimeDiff(time: string) {
  return differenceInSeconds(new Date(), new Date(time))
}

function canSubmit(comment: VespComment) {
  if (submitting.value || !comment.content || !comment.content?.blocks?.length) {
    return false
  }
  // Check if comment was modified
  return editing.value
    ? JSON.stringify(editing.value.content?.blocks) !== JSON.stringify(comment.content?.blocks)
    : true
}

async function onSubmit(comment: VespComment) {
  try {
    submitting.value = true
    if (comment.id) {
      await usePatch(url + '/' + comment.id, comment)
    } else {
      await usePut(url, comment)
    }
    onCancel()
  } catch (e) {
  } finally {
    submitting.value = false
  }
}

function onCancel() {
  if (form.value) {
    form.value.reset()
  }
  commentForm.value = {id: 0, parent_id: 0, content: {}}
  replying.value = undefined
  editing.value = undefined
  editingTime.value = undefined
  if (timer.value) {
    clearInterval(timer.value)
    timer.value = undefined
  }
}

function canEdit(comment: VespComment) {
  if (isAdmin.value) {
    return true
  }
  // Authorized user is owner of the comment
  if (!props.topic.closed && user.value && user.value.id === comment.user_id && $scope('comments/patch')) {
    // This comment has no replies
    if (!comments.value.filter((i: VespComment) => i.parent_id === comment.id).length) {
      // User still has time to edit
      if (comment.created_at && getTimeDiff(comment.created_at) < editTime) {
        return true
      }
    }
  }
  return false
}

function onEdit(comment: VespComment) {
  commentForm.value = {id: comment.id, parent_id: comment.parent_id, content: comment.content}
  editing.value = comment
  replying.value = undefined

  if (!isAdmin.value && editTime && comment.created_at) {
    timer.value = setInterval(() => {
      editingTime.value = editTime - getTimeDiff(comment.created_at)
      if (editingTime.value <= 0) {
        onCancel()
      }
    }, 1000)
  }
}

function canReply() {
  return !props.topic.closed && $scope('comments/put') && (!subRequired.value || isAdmin.value)
}

function onReply(comment: VespComment) {
  commentForm.value.id = 0
  commentForm.value.parent_id = comment.id
  editing.value = undefined
  replying.value = comment
}

function canDelete() {
  return $scope('comments/delete')
}

async function onDelete(comment: VespComment) {
  try {
    pending.value = true
    await usePatch(url + '/' + comment.id, {active: !comment.active})
  } catch (e) {
  } finally {
    pending.value = false
  }
}

function onDestroy(comment: VespComment) {
  destroying.value = comment.id
}

async function destroy() {
  try {
    pending.value = true
    await useDelete(url + '/' + destroying.value)
    destroying.value = 0
  } catch (e) {
  } finally {
    pending.value = false
  }
}

function onLink(e: MouseEvent, comment: VespComment) {
  e.preventDefault()
  setLocationHash('comment-' + comment.id)
  scrollPageTo('topic-comment-' + comment.id)
}

provide('canSubmit', canSubmit)
provide('canReply', canReply)
provide('canEdit', canEdit)
provide('onSubmit', onSubmit)
provide('onReply', onReply)
provide('onEdit', onEdit)

provide('canDelete', canDelete)
provide('onDelete', onDelete)
provide('onDestroy', onDestroy)

provide('onCancel', onCancel)
provide('onLink', onLink)

const emit = defineEmits(['comment-view'])

function scrollPage() {
  const {hash} = useRoute()
  if (hash) {
    let id: string | undefined
    if (hash === '#comments') {
      id = 'topic-comments'
    } else {
      const matches = hash.match(/#comment-(\d+)/)
      if (matches) {
        id = 'topic-comment-' + matches[1]
      }
    }
    if (id) {
      scrollPageTo(id)
    }
  }
}

function observe(items: IntersectionObserverEntry[]) {
  items.forEach((item: IntersectionObserverEntry) => {
    const visible = item.isIntersecting
    if (visible) {
      const id = Number(item.target.id.replace('topic-comment-', ''))
      const comment = comments.value.find((i: VespComment) => i.id === id)
      if (comment) {
        emit('comment-view', comment)
      }
      observer.value.unobserve(item.target)
    }
  })
}

const observer = ref()

function initObserver() {
  observer.value = new IntersectionObserver(observe)
  const targets = tree.value.$el.querySelectorAll('.comment')
  targets.forEach((target: HTMLElement) => {
    observer.value.observe(target)
  })
}

function addToObserver(comment: VespComment) {
  if (!observer.value || !user.value || user.value.id === comment.user_id) {
    return
  }
  const elem = document.getElementById('topic-comment-' + comment.id)
  if (elem) {
    observer.value.observe(elem)
  }
}

function onCommentCreate(comment: VespComment) {
  if (comment.topic_id === props.topic.id) {
    comments.value.push(comment)
    nextTick(() => {
      addToObserver(comment)
    })
  }
}

function onCommentUpdate(comment: VespComment) {
  const idx = comments.value.findIndex((i: VespComment) => i.id === comment.id)
  if (idx > -1) {
    // comments.value.splice(idx, 1, comment)
    comments.value[idx] = comment
  }
}

function onCommentDelete(comment: VespComment) {
  const idx = comments.value.findIndex((i: VespComment) => i.id === comment.id)
  if (idx > -1) {
    comments.value.splice(idx, 1)
  }
}

onMounted(() => {
  initObserver()
  if (total.value) {
    nextTick(scrollPage)
  }

  $socket.on('comment-create', onCommentCreate)
  $socket.on('comment-update', onCommentUpdate)
  $socket.on('comment-delete', onCommentDelete)
})

onUnmounted(() => {
  if (observer.value) {
    observer.value.disconnect()
  }

  $socket.off('comment-create', onCommentCreate)
  $socket.off('comment-update', onCommentUpdate)
  $socket.off('comment-delete', onCommentDelete)
})
</script>
