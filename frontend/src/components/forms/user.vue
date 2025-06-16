<template>
  <div>
    <BRow>
      <BCol md="8" class="order-1 order-md-0">
        <BFormGroup :label="$t('models.user.username')">
          <BFormInput v-model.trim="record.username" required autofocus />
        </BFormGroup>
        <BFormGroup :label="$t('models.user.password')">
          <VespInputPassword v-model.trim="record.password" :required="!record.id" />
        </BFormGroup>
      </BCol>
      <BCol md="4" class="text-center order-0 order-md-1">
        <slot name="avatar">
          <FileUpload
            v-model="record.new_avatar"
            :placeholder="record.avatar"
            :height="150"
            :width="150"
            wrapper-class="rounded-circle m-auto"
          />
        </slot>
      </BCol>
    </BRow>

    <BFormGroup :label="$t('models.user.fullname')">
      <BFormInput v-model="record.fullname" required />
    </BFormGroup>

    <BFormGroup :label="$t('models.user.email')">
      <BFormInput v-model="record.email" type="email" />
    </BFormGroup>

    <BFormGroup v-if="showGroup" :label="$t('models.user.role')">
      <VespInputComboBox v-model="record.role_id" url="admin/user-roles" required />
    </BFormGroup>

    <BRow v-if="showStatus || showNotify" class="flex-md-nowrap justify-content-md-between">
      <BFormGroup v-if="showStatus" class="col-md-auto">
        <BFormCheckbox v-model="record.active">
          {{ $t('models.user.active') }}
        </BFormCheckbox>
      </BFormGroup>
      <BFormGroup v-if="showStatus" class="col-md-auto">
        <BFormCheckbox v-model="record.blocked">
          {{ $t('models.user.blocked') }}
        </BFormCheckbox>
      </BFormGroup>
      <BFormGroup v-if="showNotify" class="col-md-auto">
        <BFormCheckbox v-model="record.notify">
          {{ $t('models.user.notify') }}
        </BFormCheckbox>
      </BFormGroup>
    </BRow>
    <template v-if="showReadonly">
      <BRow class="align-items-center">
        <BCol md="6">
          <BFormGroup>
            <BFormCheckbox v-model="record.readonly">
              {{ $t('models.user.readonly') }}
            </BFormCheckbox>
          </BFormGroup>
        </BCol>
        <BCol v-if="record.readonly" md="6">
          <BFormGroup>
            <VespInputDatePicker
              v-model="record.readonly_until"
              type="datetime"
              :placeholder="$t('models.user.readonly_until')"
            >
              {{ $t('models.user.readonly') }}
            </VespInputDatePicker>
          </BFormGroup>
        </BCol>
      </BRow>
      <BFormGroup v-if="record.readonly" :label="$t('models.user.readonly_reason')">
        <BFormInput v-model="record.readonly_reason" />
      </BFormGroup>
    </template>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  modelValue: {
    type: Object as PropType<VespUser>,
    required: true,
  },
  showGroup: {
    type: Boolean,
    default: true,
  },
  showStatus: {
    type: Boolean,
    default: true,
  },
  showNotify: {
    type: Boolean,
    default: true,
  },
  showReadonly: {
    type: Boolean,
    default: true,
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

function generatePassword(length = 12) {
  const charset = 'abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
  let password = ''
  for (let i = 0, n = charset.length; i < length; ++i) {
    password += charset.charAt(Math.floor(Math.random() * n))
  }
  record.value.password = password
}

onMounted(() => {
  if (!record.value.id) {
    generatePassword()
  }
})
</script>
