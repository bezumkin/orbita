<template>
  <BCol xl="10" class="m-auto">
    <BOverlay :show="loading" opacity="0.5">
      <BForm @submit.prevent="onSubmit">
        <FormsUser v-model="form" :show-status="false" :show-group="false">
          <template #avatar>
            <div style="cursor: pointer" @click="showAvatar">
              <UserAvatar :user="user" :size="$isMobile ? 150 : 120" />
            </div>
            <div class="text-center">
              <BButton variant="link" size="sm" @click="showAvatar">{{ t('actions.edit') }}</BButton>
            </div>
          </template>
        </FormsUser>
        <div class="text-center mt-3">
          <BButton variant="primary" type="submit">{{ t('actions.save') }}</BButton>
        </div>
      </BForm>
    </BOverlay>

    <BModal v-model="showAvatarUpload" v-bind="avatarUploadProps" @ok="onAvatarSubmit" @hidden="hideAvatar">
      <BOverlay :show="avatarLoading" :opacity="0.5">
        <UserAvatarCrop v-model="avatar" :size="300">
          <template #actions="{select}">
            <div class="d-flex justify-content-center gap-2 mt-2">
              <BButton variant="secondary" size="sm" @click="select">
                <VespFa icon="upload" /> {{ t('actions.pick') }}
              </BButton>
              <BButton v-if="user.avatar || avatar.file" variant="danger" size="sm" @click="onAvatarDelete">
                <VespFa icon="trash" /> {{ t('actions.delete') }}
              </BButton>
            </div>
          </template>
        </UserAvatarCrop>
      </BOverlay>
    </BModal>

    <div v-if="hasServices" class="mt-5 pt-5 border-top">
      <UserConnections />
    </div>
  </BCol>
</template>

<script setup lang="ts">
const {t} = useI18n()
const {$isMobile, $settings, $variables} = useNuxtApp()
const {user, loadUser} = useAuth()
const loading = ref(false)
const form = ref<VespUser>({id: 0, username: '', ...useAuth().user.value})
const hasServices = $variables.value.CONNECTION_SERVICES !== ''

async function onSubmit() {
  try {
    loading.value = true
    const {user} = await usePatch('user/profile', form.value)
    if (user) {
      form.value = user
      useToastSuccess(t('success.profile'))
      await loadUser()
    }
  } catch (e) {
  } finally {
    loading.value = false
  }
}

const urlAvatar = 'user/avatar'
const avatar = ref({})
const avatarLoading = ref(false)
const showAvatarUpload = ref(false)
const avatarUploadProps = computed(() => {
  return {
    hideHeader: true,
    footerClass: 'justify-content-between',
    okTitle: t('actions.save'),
    okDisabled: !Object.keys(avatar.value).length || !avatar.value.file,
    cancelTitle: t('actions.close'),
  }
})

async function showAvatar() {
  try {
    const data = await useGet(urlAvatar)
    avatar.value = {file: data.file || '', metadata: data.metadata || {}}
  } catch (e) {
    console.error(e)
  }
  showAvatarUpload.value = true
}

function hideAvatar() {
  showAvatarUpload.value = false
}

async function onAvatarSubmit(e) {
  e.preventDefault()
  avatarLoading.value = true
  try {
    const data = await usePost(urlAvatar, avatar.value)
    hideAvatar()
    avatar.value = data
    await loadUser()
  } catch (e) {
  } finally {
    avatarLoading.value = false
  }
}

async function onAvatarDelete() {
  if (user.value?.avatar) {
    avatarLoading.value = true
    try {
      await useDelete(urlAvatar)
      await loadUser()
      avatar.value = {file: '', metadata: {}}
    } catch (e) {
    } finally {
      avatarLoading.value = false
    }
  } else {
    avatar.value = {file: '', metadata: {}}
  }
}

useHead({
  title: () => [t('pages.user.profile'), t('pages.user.title'), $settings.value.title].join(' / '),
})
</script>
