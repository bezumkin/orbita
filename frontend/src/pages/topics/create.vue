<template>
  <div class="column">
    <b-overlay :show="loading" opacity="0.5" class="topic">
      <b-form class="topic-form" @submit.prevent="onSubmit" @keydown="onKeydown">
        <forms-topic v-model="record" />
        <div class="topic-buttons mb-0 mt-2">
          <b-button :disabled="loading" @click.prevent="onCancel">{{ $t('actions.cancel') }}</b-button>
          <b-button variant="primary" type="submit" :disabled="loading">
            <b-spinner v-if="loading" small />
            {{ $t('actions.save') }}
          </b-button>
        </div>
      </b-form>
    </b-overlay>
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
</script>
