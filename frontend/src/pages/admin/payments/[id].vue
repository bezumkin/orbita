<template>
  <VespModal v-model="record" :title="t('models.payment.title_one')" cancel-title="actions.close">
    <template #form-fields>
      <FormsPayment v-model="record" />
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const id = String(useRoute().params.id)
const {t} = useI18n()

const url = 'admin/payments/' + id
const record = ref<VespPayment>({id, amount: 0})

try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}
</script>
