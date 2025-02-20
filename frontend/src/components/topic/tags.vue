<template>
  <div>
    <BFormTags
      ref="input"
      v-model="record"
      :separator="[',', ';', '.']"
      :placeholder="$t('models.tag.placeholder')"
      :add-button-text="$t('models.tag.add')"
      :duplicate-tag-text="$t('models.tag.duplicate')"
      :invalid-tag-text="$t('models.tag.invalid')"
      remove-on-delete
      no-add-on-enter
      @input="onInput"
      @focus="onFocus"
      @blur="onBlur"
      @keydown="onKeydown"
    />
    <ul :class="{'dropdown-menu vesp-combo-list': true, show: filteredTags.length > 0}">
      <BDropdownItem
        v-for="(item, idx) in filteredTags"
        :key="item.id"
        :class="{'vesp-combo-list-item': true}"
        :active="selected === idx"
        @click="onSelect(item)"
      >
        {{ item.title }}
      </BDropdownItem>
    </ul>

    <div v-if="availableTags.length" class="mt-1">
      <BButton variant="link" class="px-0" @click="availableVisible = !availableVisible">
        {{ $t('models.tag.available') }}
      </BButton>
      <BCollapse v-model="availableVisible">
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
      </BCollapse>
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

const availableVisible = ref(false)
const tags: Ref<VespTag[]> = ref([])
const availableTags = computed(() => {
  return tags.value.filter((i) => !record.value.includes(i.title))
})
const input = ref()
const search = ref('')
const filteredTags = computed(() => {
  const text = search.value.toLowerCase()
  return text.length ? availableTags.value.filter((i) => i.title.toLowerCase().includes(text)) : []
})
const selected = ref<number | undefined>()

function onTagAdd(tag: VespTag) {
  const tmp = record.value
  tmp.push(tag.title)
  record.value = tmp
}

function onInput(e: InputEvent) {
  search.value = (e.target as HTMLInputElement).value
}

function onFocus() {
  if (input.value) {
    search.value = input.value.inputValue
  }
}

function onBlur() {
  selected.value = undefined
  setTimeout(() => {
    search.value = ''
  }, 100)
}

function onSelect(tag: VespTag) {
  onTagAdd(tag)
  onReset()
}

function onReset() {
  onBlur()
  if (input.value) {
    input.value.element.focus()
    input.value.inputValue = ''
  }
}

async function loadTags() {
  const {rows} = await useGet('admin/tags', {combo: true, limit: 0})
  tags.value = rows
}

function onKeydown(e: KeyboardEvent) {
  if (['Enter', 'Escape'].includes(e.key) || (['ArrowDown', 'ArrowUp'].includes(e.key) && filteredTags.value.length)) {
    e.stopPropagation()
    e.preventDefault()
  }

  if (e.key === 'ArrowDown' && (selected.value === undefined || selected.value < filteredTags.value.length - 1)) {
    selected.value = selected.value === undefined ? 0 : selected.value + 1
  } else if (e.key === 'ArrowUp' && selected.value !== undefined) {
    selected.value = selected.value === 0 ? undefined : selected.value - 1
  } else if (e.key === 'Enter') {
    if (selected.value !== undefined) {
      onSelect(filteredTags.value[selected.value])
      onBlur()
    } else if (search.value) {
      if (!record.value.includes(search.value)) {
        onTagAdd({id: 0, title: search.value})
        onReset()
      }
    }
  } else if (e.key === 'Escape') {
    onReset()
  }
}

onMounted(loadTags)
</script>
