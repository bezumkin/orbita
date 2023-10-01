<template>
  <div>
    <b-row>
      <b-col md="8" class="order-1 order-md-0">
        <b-form-group :label="$t('models.user.username')">
          <b-form-input v-model="record.username" required autofocus />
        </b-form-group>
        <b-form-group :label="$t('models.user.password')">
          <vesp-input-password v-model="record.password" :required="!record.id" />
        </b-form-group>
      </b-col>
      <b-col md="4" class="d-flex justify-content-center justify-content-md-end order-0 order-md-1">
        <b-form-group>
          <file-upload
            v-model="record.new_avatar"
            :placeholder="record.avatar"
            :height="150"
            :width="150"
            wrapper-class="rounded-circle"
          />
        </b-form-group>
      </b-col>
    </b-row>

    <b-form-group :label="$t('models.user.fullname')">
      <b-form-input v-model="record.fullname" required />
    </b-form-group>

    <b-row>
      <b-col md="6">
        <b-form-group :label="$t('models.user.email')">
          <b-form-input v-model.trim="record.email" type="email" />
        </b-form-group>
      </b-col>
      <b-col md="6">
        <b-form-group :label="$t('models.user.phone')">
          <b-form-input v-model="record.phone" />
        </b-form-group>
      </b-col>
    </b-row>

    <b-form-group v-if="showGroup" :label="$t('models.user.role')">
      <vesp-input-combo-box v-model="record.role_id" url="admin/user-roles" required />
    </b-form-group>

    <b-row v-if="showStatus">
      <b-col md="6">
        <b-form-group>
          <b-form-checkbox v-model.trim="record.active">
            {{ $t('models.user.active') }}
          </b-form-checkbox>
        </b-form-group>
      </b-col>
      <b-col md="6">
        <b-form-group>
          <b-form-checkbox v-model.trim="record.blocked">
            {{ $t('models.user.blocked') }}
          </b-form-checkbox>
        </b-form-group>
      </b-col>
    </b-row>
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
