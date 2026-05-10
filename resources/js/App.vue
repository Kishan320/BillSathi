<template>
  <div id="app">
    <router-view />
    <Snackbar />
  </div>
</template>

<script setup>
import Snackbar from '@/components/ui/Snackbar.vue';
</script>

<style>
#app {
  min-height: 100vh;
}
</style>
