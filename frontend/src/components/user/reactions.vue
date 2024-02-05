<template>
  <div ref="wrapper" :class="wrapperClass" v-on="!$isMobile ? {mouseenter: onEnter, mouseleave: onLeave} : undefined">
    <div class="button" v-on="$isMobile ? {click: onClick} : undefined">
      <slot name="default" v-bind="{total, selected}">
        {{ total }}
      </slot>
    </div>
    <div class="reactions">
      <div
        v-for="reaction in $reactions"
        :key="reaction.id"
        :title="reaction.title"
        :class="reactionClass(reaction)"
        @click="onSelect(reaction.id)"
      >
        <div class="emoji">{{ reaction.emoji }}</div>
        <div v-if="reactions && reactions[reaction.id]" class="counter">{{ reactions[reaction.id] }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  item: {
    type: Object as PropType<VespTopic | VespComment>,
    required: true,
  },
  small: {
    type: Boolean,
    default: false,
  },
})

const {loggedIn} = useAuth()
const {$reactions, $isMobile, $login} = useNuxtApp()
const url = 'web/' + ('uuid' in props.item ? 'topics/' + props.item.uuid : 'comments/' + props.item.id) + '/reactions'
const reactions: Ref<Record<string, number> | undefined> = ref()
const loading = ref(false)
const selected = ref(props.item.reaction || 0)
const total = ref(props.item.reactions_count || 0)
const visible = ref(false)
const wrapper = ref()
const wrapperClass = computed(() => {
  const classes = ['user-reactions']
  if (props.small) {
    classes.push('small')
  }
  if (visible.value) {
    classes.push('visible')
  }
  return classes
})

function reactionClass(item: VespReaction) {
  const classes = ['reaction']
  if (selected.value === item.id) {
    classes.push('selected')
  }

  return classes
}

async function onLoad() {
  if (reactions.value === undefined && !loading.value) {
    loading.value = true
    try {
      const data = await useGet(url)
      reactions.value = data.reactions
      selected.value = data.reaction
    } catch (e) {
    } finally {
      loading.value = false
    }
  }
}

async function onSelect(id: number) {
  if (!loggedIn.value) {
    $login.value = true
  } else if (!loading.value) {
    selected.value = selected.value === id ? 0 : id
    loading.value = true
    try {
      const data = await useApi(url, {method: selected.value ? 'POST' : 'DELETE', body: {reaction_id: id}})
      reactions.value = data.reactions
      total.value = 0
      Object.values(reactions.value || {}).forEach((i) => {
        total.value += i
      })
      if ($isMobile.value) {
        visible.value = false
      }
    } catch (e) {
    } finally {
      loading.value = false
    }
  }
}

function onEnter() {
  onLoad()
  visible.value = true
}

function onLeave() {
  visible.value = false
}

function onClick() {
  if (!visible.value) {
    onLoad()
    visible.value = true
    if (reactions.value === undefined) {
      window.addEventListener('click', listener)
      onBeforeUnmount(() => {
        window.removeEventListener('click', listener)
      })
    }
  } else {
    visible.value = false
  }
}

const listener = (e: Event) => {
  if (e.target === wrapper.value || !e.composedPath().includes(wrapper.value)) {
    visible.value = false
  }
}
</script>
