<template>
  <div>
    <b-form-group v-for="(setting, idx) in settings" :key="idx" class="bg-light p-3 border rounded">
      <template #label>
        <h5 :class="{'mb-0': true, 'mt-4': idx > 0}">
          {{ $t('models.setting.' + setting.key) }}
        </h5>
      </template>
      <template #description>
        <div class="mt-2">{{ $t('models.setting.' + setting.key + '_desc') }}</div>
      </template>
      <b-row class="align-items-center justify-content-end">
        <b-col class="flex-grow-1">
          <b-overlay :show="saving === setting.key" :opacity="0.5">
            <forms-setting v-model="settings[idx]" :editing="editing === setting.key" @submit="saveSetting(setting)" />
          </b-overlay>
        </b-col>
        <b-col cols="auto" class="text-md-end d-flex gap-1 flex-column">
          <template v-if="editing === setting.key">
            <b-button size="sm" :disabled="!canSave(setting)" variant="success" @click="saveSetting(setting)">
              <fa icon="check" />
            </b-button>
            <b-button size="sm" @click="cancelEdit(setting)"><fa icon="times" /></b-button>
          </template>
          <b-button v-else size="sm" @click="startEdit(setting)"><fa icon="edit" /></b-button>
        </b-col>
      </b-row>
    </b-form-group>
  </div>
</template>

<script setup lang="ts">
const {t} = useI18n()
const {$setting} = useNuxtApp()

const editing = ref('')
const saving = ref('')
const save = ref('')
const settings: Ref<VespSetting[]> = ref([])

async function fetch() {
  const {rows} = await useGet('admin/settings')
  settings.value = rows
}
fetch()

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
    await fetch()
  } catch (e) {
  } finally {
    saving.value = ''
  }
}

useHead({
  title: () => [t('pages.admin.settings'), t('pages.admin.title'), $setting('title')].join(' / '),
})
</script>
