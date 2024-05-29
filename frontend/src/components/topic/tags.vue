<template>
  <div>
    <input
      list="topic-tags"
      :placeholder="$t('models.topic.tags_desc')"
      class="form-control"
      @keydown="onTagAdd"
      @input="onTagSelect"
    />
    <datalist id="topic-tags">
      <option v-for="tag in availableTags" :key="tag.id" :value="tag.title" />
    </datalist>
    <div class="d-flex flex-wrap gap-1 mt-2">
      <BBadge v-for="(tag, idx) in record" :key="idx" class="px-2 py-1 d-flex align-items-center">
        {{ tag.title }}
        <BButton class="p-0 ms-1" size="sm" style="line-height: unset" @click="onTagRemove(idx)">
          <VespFa icon="times" class="fa-fw" />
        </BButton>
      </BBadge>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Array as PropType<VespTag[]>,
    default() {
      return []
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

const tags: Ref<VespTag[]> = ref([])
const availableTags = computed(() => {
  const topicTags = record.value.map((i) => i.title) || []
  return tags.value.filter((i) => !topicTags.includes(i.title))
})

function onTagAdd(e: KeyboardEvent) {
  if (e.code !== 'Enter') {
    return
  }
  e.preventDefault()
  const input = e.target as HTMLInputElement
  tagAdd(input.value)
  input.value = ''
}

function onTagSelect(e: Event) {
  if ((e as InputEvent).inputType !== 'insertReplacementText') {
    return
  }
  const input = e.target as HTMLInputElement
  tagAdd(input.value)
  input.value = ''
}

function tagAdd(title: string) {
  title = title.trim()
  if (!title || record.value.findIndex((i: VespTag) => i.title === title) > -1) {
    return
  }
  record.value.push({id: 0, title})
}

function onTagRemove(idx: number) {
  record.value.splice(idx, 1)
}

async function loadTags() {
  const {rows} = await useGet('admin/tags', {combo: true, limit: 0})
  tags.value = rows
}

onMounted(loadTags)
</script>
