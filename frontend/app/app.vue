<script setup>
useHead({
  title: 'Expense Reimbursement',
})

useSeoMeta({
  title: 'Reimbursement',
  ogTitle: 'Reimbursement',
  description: 'Submit and track your out-of-pocket expense reimbursement requests quickly and securely.',
  ogDescription: 'Our Nuxt 3 & Laravel-powered portal makes it easy to file, review, and approve expense reimbursements without the hassle.',
  ogImage: 'https://yourdomain.com/images/seo/expense-reimbursement.png',
  twitterCard: 'summary_large_image',
})
</script>

<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<style lang='scss'>
@use 'App.scss';
</style>
