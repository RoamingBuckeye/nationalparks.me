<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { CircleCheckIcon, InfoIcon, Loader2Icon, OctagonXIcon, TriangleAlertIcon, XIcon } from "@lucide/vue"
import { Toaster as Sonner } from "vue-sonner"
import { cn } from "@/lib/utils"

import 'vue-sonner/style.css';

const props = defineProps<ToasterProps>()
</script>

<template>
  <Sonner
    :class="cn('toaster', props.class)"
    :style="{
      '--normal-bg': 'var(--popover)',
      '--normal-text': 'var(--popover-foreground)',
      '--normal-border': 'var(--border)',
      '--border-radius': 'var(--radius)',
    }"
    v-bind="props"
  >
    <template #success-icon>
      <CircleCheckIcon class="toaster__icon" />
    </template>
    <template #info-icon>
      <InfoIcon class="toaster__icon" />
    </template>
    <template #warning-icon>
      <TriangleAlertIcon class="toaster__icon" />
    </template>
    <template #error-icon>
      <OctagonXIcon class="toaster__icon" />
    </template>
    <template #loading-icon>
      <div>
        <Loader2Icon class="toaster__icon toaster__icon--spin" />
      </div>
    </template>
    <template #close-icon>
      <XIcon class="toaster__icon" />
    </template>
  </Sonner>
</template>
