<template>
  <div>
    <BFormTags
      v-model="record"
      :separator="[',', ';', '.']"
      :placeholder="$t('models.topic.tags.placeholder')"
      :add-button-text="$t('models.topic.tags.add')"
      :duplicate-tag-text="$t('models.topic.tags.duplicate')"
      :invalid-tag-text="$t('models.topic.tags.invalid')"
      remove-on-delete
      @keydown="onKeydown"
    />
    <div v-if="availableTags.length" class="mt-1 mb-3">
      <div class="form-label bv-no-focus-ring col-form-label">
        {{ $t('models.topic.tags.available') }}
      </div>
      <div class="d-flex flex-wrap gap-2 mt-1">
        <BBadge
          v-for="(tag, idx) in availableTags"
          :key="idx"
          style="cursor: pointer"
          variant="light"
          class="px-2 py-1 d-flex align-items-center"
          @click="onTagAdd(tag)"
        >
          {{ tag.title }}
        </BBadge>
      </div>
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
    return reactive(props.modelValue.map((i) => i.title))
  },
  set(newValue) {
    const tmp: VespTag[] = []
    newValue.forEach((title) => {
      const find = tags.value.find((t) => t.title === title)
      tmp.push({id: find ? find.id : 0, title})
    })
    emit('update:modelValue', tmp)
  },
})

const tags: Ref<VespTag[]> = ref([])
const availableTags = computed(() => {
  return tags.value.filter((i) => !record.value.includes(i.title))
})

function onTagAdd(tag: VespTag) {
  const tmp = record.value
  tmp.push(tag.title)
  record.value = tmp
}

async function loadTags() {
  const {rows} = await useGet('admin/tags', {combo: true, limit: 0})
  tags.value = rows
}

function onKeydown(e: KeyboardEvent) {
  if (e.code === 'Enter') {
    e.preventDefault()
    e.stopPropagation()
  }
}

onMounted(loadTags)
</script>
