<template>
  <div class="d-flex flex-column min-vh-100">
    <app-navbar class="border-bottom" />
    <b-container class="pt-4 flex-grow-1">
      <slot name="default" />
    </b-container>

    <app-footer class="bg-light border-top" />
  </div>
</template>

<script setup lang="ts"></script>
