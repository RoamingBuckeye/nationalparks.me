import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva("button", {
  variants: {
    variant: {
      default: "button--default",
      destructive: "button--destructive",
      outline: "button--outline",
      secondary: "button--secondary",
      ghost: "button--ghost",
      link: "button--link",
    },
    size: {
      "default": "button--size-default",
      "sm": "button--size-sm",
      "lg": "button--size-lg",
      "icon": "button--icon",
      "icon-sm": "button--icon-sm",
      "icon-lg": "button--icon-lg",
    },
  },
  defaultVariants: {
    variant: "default",
    size: "default",
  },
})
export type ButtonVariants = VariantProps<typeof buttonVariants>
