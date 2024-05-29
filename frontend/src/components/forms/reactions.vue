<template>
  <div>
    <BOverlay :show="pending || loading" opacity="0.5">
      <Draggable :list="reactions" item-key="id" class="row g-2" @end="onSort">
        <template #item="{element}">
          <BCol cols="6" md="auto">
            <BCard footer-class="p-1">
              <template #default>
                <div :class="{'text-center': true, 'opacity-25': !element.active}">
                  <div :style="{'font-size': emojiSize, 'min-width': colSize}">{{ element.emoji }}</div>
                  <div class="small">{{ element.title }}</div>
                </div>
              </template>
              <template #footer>
                <div class="d-flex w-100 justify-content-between">
                  <BButton size="sm" :variant="null" @click.prevent="onEdit(element)">
                    <VespFa icon="edit" fixed-width />
                  </BButton>
                  <BButton size="sm" :variant="null" @click.prevent="onDelete(element)">
                    <VespFa icon="times" fixed-width class="text-danger" />
                  </BButton>
                </div>
              </template>
            </BCard>
          </BCol>
        </template>
        <template #footer>
          <BCol cols="6" md="auto">
            <BCard class="h-100">
              <template #default>
                <div :style="{'min-width': colSize}" class="h-100 d-flex align-items-center justify-content-center">
                  <BButton
                    variant="success"
                    class="rounded-circle p-0 text-center"
                    :style="{width: emojiSize, height: emojiSize}"
                    @click="onCreate"
                  >
                    <VespFa icon="plus" size="3x" />
                  </BButton>
                </div>
              </template>
            </BCard>
          </BCol>
        </template>
      </Draggable>
    </BOverlay>

    <VespModal
      v-if="form"
      v-model="form"
      :url="url"
      :title="$t('models.reaction.title_one')"
      :method="form.id ? 'patch' : 'put'"
      update-key="admin-reactions"
      @hidden="onHide"
    >
      <template #form-fields>
        <BFormGroup :label="$t('models.reaction.title')">
          <BFormInput v-model="form.title" />
        </BFormGroup>

        <BFormGroup :label="$t('models.reaction.emoji')" :description="$t('models.reaction.emoji_desc')">
          <BFormInput v-model="form.emoji" required maxlength="2" />
        </BFormGroup>

        <!--<b-form-checkbox v-model="form.active">
          {{ $t('models.reaction.active') }}
        </b-form-checkbox>-->
      </template>
    </VespModal>

    <VespModal
      v-else-if="deleting"
      v-model="deleting"
      :url="url"
      :title="$t('components.table.delete.title')"
      method="delete"
      update-key="admin-reactions"
      ok-variant="danger"
      :ok-title="$t('actions.delete')"
      centered
      @hidden="onHide"
    >
      {{ $t('components.table.delete.confirm') }}
    </VespModal>
  </div>
</template>

<script setup lang="ts">
import Draggable from 'vuedraggable'

const url = 'admin/reactions'
const {data, pending} = useCustomFetch(url)
const loading = ref(false)
const reactions: ComputedRef<VespReaction[]> = computed(() => {
  return data.value?.rows || []
})
const form: Ref<VespReaction | undefined> = ref()
const deleting: Ref<VespReaction | undefined> = ref()

const colSize = '7rem'
const emojiSize = '4rem'

function onCreate() {
  form.value = {
    id: 0,
    title: '',
    emoji: '',
    active: true,
  }
}

function onEdit(item: VespReaction) {
  form.value = {...item}
}

function onDelete(item: VespReaction) {
  deleting.value = {...item}
}

async function onSort() {
  loading.value = true
  const data = reactions.value.map((i, idx) => {
    return {id: i.id, rank: idx}
  })
  try {
    await usePost(url, {reactions: data})
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onHide() {
  form.value = undefined
  deleting.value = undefined
}
</script>
