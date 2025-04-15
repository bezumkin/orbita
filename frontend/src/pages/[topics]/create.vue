<template>
  <div class="column">
    <BOverlay :show="loading" opacity="0.5" class="topic">
      <BForm class="topic-form" @submit.prevent="onSubmit" @keydown="onKeydown">
        <FormsTopic v-model="record" />
        <div class="topic-buttons mb-0 mt-2">
          <BButton :disabled="loading" @click.prevent="onCancel">{{ $t('actions.cancel') }}</BButton>
          <BButton variant="primary" type="submit" :disabled="loading">
            <BSpinner v-if="loading" small />
            {{ $t('actions.save') }}
          </BButton>
        </div>
      </BForm>
    </BOverlay>
  </div>
</template>

<script setup lang="ts">
const {user} = useAuth()
if (!user.value) {
  showError({statusCode: 401, statusMessage: 'Unauthorized'})
} else if (!hasScope('topics/put')) {
  showError({statusCode: 403, statusMessage: 'Access Denied'})
}

const store = useTopicsStore()
const category = computed(() => store.category)

const router = useRouter()
const loading = ref(false)
const record = ref({
  id: 0,
  title: '',
  teaser: '',
  type: null,
  price: 0,
  content: {},
  user_id: user.value?.id,
  category_id: category.value?.id,
  active: false,
  hide_comments: false,
  hide_views: false,
  hide_reactions: false,
  delayed: false,
  publish_at: null,
  published_at: null,
  tags: [],
})

async function onSubmit() {
  try {
    loading.value = true
    const data = await usePut('admin/topics', {...record.value})
    await router.replace({name: 'topics-uuid', params: {topics: data.category?.uri || 'topics', uuid: data.uuid}})
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onCancel() {
  router.replace({name: 'index'})
}

function onKeydown(e: KeyboardEvent) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault()
    onSubmit()
  }
}
</script>
