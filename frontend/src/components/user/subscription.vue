<template>
  <div>{{ user?.subscription }}</div>
</template>

<script setup lang="ts">
const {user} = useAuth()
</script>
