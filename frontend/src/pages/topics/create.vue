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
const router = useRouter()
const loading = ref(false)
const record = ref({
  id: 0,
  title: '',
  price: 0,
  content: {},
  active: false,
  closed: false,
  tags: [],
})

async function onSubmit() {
  try {
    loading.value = true
    const data = await usePut('admin/topics', {...record.value})
    await router.replace({name: 'topics-uuid', params: {uuid: data.uuid}})
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

const {user} = useAuth()
if (!user.value) {
  showError({statusCode: 401, statusMessage: 'Unauthorized'})
} else if (!hasScope('topics/put')) {
  showError({statusCode: 403, statusMessage: 'Access Denied'})
}
</script>
