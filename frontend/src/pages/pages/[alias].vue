<template>
  <BOverlay :show="loading" opacity="0.5" class="col-md-9 m-auto topic">
    <div v-if="!record">
      <h1 class="topic-header">
        {{ page.title }}
        <BButton v-if="$scope('pages/get')" variant="link" class="ms-2 p-0" @click="onEdit">
          <VespFa icon="edit" class="fa-fw" />
        </BButton>
      </h1>
      <EditorContent :content="page.content" />
    </div>
    <BForm v-else-if="$scope('pages/get')" class="topic-form" @submit.prevent="onSubmit" @keydown="onKeydown">
      <div class="topic-buttons">
        <BButton :disabled="loading" @click.prevent="onCancel">{{ $t('actions.cancel') }}</BButton>
        <BButton variant="primary" type="submit" :disabled="loading">
          <BSpinner v-if="loading" small />
          {{ $t('actions.save') }}
        </BButton>
      </div>
      <FormsPage v-model="record" />
    </BForm>
  </BOverlay>
</template>

<script setup lang="ts">
const route = useRoute()
const {$settings, $socket} = useNuxtApp()
const {data, error} = await useCustomFetch('web/pages/' + route.params.alias)
if (error.value) {
  showError({statusCode: error.value.statusCode || 500, statusMessage: error.value.statusMessage || 'Server Error'})
}
const page: ComputedRef<VespPage> = computed(() => data.value || {})

const loading = ref(false)
const record: Ref<undefined | VespPage> = ref()

async function onEdit() {
  try {
    loading.value = true
    record.value = await useGet('admin/pages/' + page.value.id)
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  try {
    loading.value = true
    await usePatch('admin/pages/' + page.value.id, {...record.value})
    onCancel()
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onCancel() {
  record.value = undefined
}

function onUpdatePage(data: VespPage) {
  if (data.id !== page.value.id) {
    return
  }
  if (page.value.alias !== data.alias) {
    useRouter().replace({name: 'pages-alias', params: {alias: data.alias}})
  } else {
    page.value.title = data.title
    page.value.content = data.content
  }
}

function onKeydown(e: KeyboardEvent) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault()
    onSubmit()
  }
}

onMounted(() => {
  $socket.on('page-update', onUpdatePage)
  if (error.value) {
    clearError()
  }
})

onUnmounted(() => {
  $socket.off('page-update', onUpdatePage)
})

useHead({
  title: () => [page.value?.title, $settings.value.title].join(' / '),
})
</script>
