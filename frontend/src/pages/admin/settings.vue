<template>
  <div>
    <BFormGroup v-for="(setting, idx) in settings" :key="idx" class="p-3 border rounded">
      <template #label>
        <h5 class="mb-0">
          {{ $t('models.setting.' + setting.key) }}
        </h5>
      </template>
      <template #description>
        <div class="mt-2">{{ $t('models.setting.' + setting.key + '_desc') }}</div>
      </template>
      <BRow class="align-items-center justify-content-end">
        <BCol class="flex-grow-1">
          <BOverlay :show="saving === setting.key" opacity="0.5">
            <FormsSetting v-model="settings[idx]" :editing="editing === setting.key" @submit="saveSetting(setting)" />
          </BOverlay>
        </BCol>
        <BCol cols="auto" class="text-md-end d-flex gap-1 flex-column">
          <template v-if="editing === setting.key">
            <BButton size="sm" :disabled="!canSave(setting)" variant="success" @click="saveSetting(setting)">
              <VespFa icon="check" />
            </BButton>
            <BButton size="sm" @click="cancelEdit(setting)"><VespFa icon="times" /></BButton>
          </template>
          <BButton v-else size="sm" @click="startEdit(setting)"><VespFa icon="edit" /></BButton>
        </BCol>
      </BRow>
    </BFormGroup>

    <BFormGroup v-if="$scope('reactions/get')" class="p-3 border rounded">
      <template #label>
        <h5 class="mb-0">
          {{ $t('models.setting.reactions') }}
        </h5>
      </template>
      <template #description>
        <div class="mt-2">{{ $t('models.setting.reactions_desc') }}</div>
      </template>
      <FormsReactions />
    </BFormGroup>
  </div>
</template>

<script setup lang="ts">
const editing = ref('')
const saving = ref('')
const save = ref('')
const settings: Ref<VespSetting[]> = ref([])

const {data, refresh} = await useCustomFetch('admin/settings')
settings.value = data.value.rows

function startEdit(setting: VespSetting) {
  save.value = JSON.parse(JSON.stringify(setting.value))
  editing.value = setting.key
}

function canSave(setting: VespSetting) {
  return !setting.required || setting.value
}

function cancelEdit(setting: VespSetting) {
  setting.value = JSON.parse(JSON.stringify(save.value))
  save.value = ''
  editing.value = ''
}

async function saveSetting(setting: VespSetting) {
  if (!canSave(setting)) {
    return
  }
  try {
    saving.value = setting.key
    await usePatch('admin/settings/' + setting.key, {value: setting.value})
    save.value = ''
    editing.value = ''
    refresh()
  } catch (e) {
  } finally {
    saving.value = ''
  }
}
</script>
