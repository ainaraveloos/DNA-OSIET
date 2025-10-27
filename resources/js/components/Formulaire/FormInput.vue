  <template>
    <component
      :is="inputComponent"
      v-bind="inputProps"
      :value="modelValue"
      @update:value="updateValue"
      class="w-full"
    />
  </template>

  <script setup>
  import { Input, InputNumber } from 'ant-design-vue'
import { computed } from 'vue'

  // Pour les variantes de Input (textarea et password)
  const { TextArea, Password } = Input

  const props = defineProps({
    modelValue: [String, Number],
    variant: {
      type: String,
      default: 'input',
      validator: v => ['input', 'textarea', 'number', 'password'].includes(v)
    },
    placeholder: String,
    disabled: Boolean,
    size: {
      type: String,
      default: 'middle',
      validator: v => ['small', 'middle', 'large'].includes(v)
    },
    min: Number,
    max: Number,
    step: Number,
    precision: Number,
    rows: {
      type: Number,
      default: 4
    },
    autoSize: {
      type: [Boolean, Object],
      default: false
    },
    visibilityToggle: {
      type: Boolean,
      default: true
    }
  })

  const emit = defineEmits(['update:modelValue'])

  // 🔄 Fonction pour émettre les changements de valeur
  const updateValue = (value) => {
    emit('update:modelValue', value)
  }

  // Sélection du bon composant
  const inputComponent = computed(() => {
    switch (props.variant) {
      case 'textarea':
        return TextArea
      case 'number':
        return InputNumber
      case 'password':
        return Password
      default:
        return Input
    }
  })

  // Props spécifiques selon le type
  const inputProps = computed(() => {
    const base = {
      placeholder: props.placeholder,
      disabled: props.disabled,
      size: props.size
    }

    if (props.variant === 'number') {
      return { ...base, min: props.min, max: props.max, step: props.step, precision: props.precision }
    }

    if (props.variant === 'textarea') {
      return { ...base, rows: props.rows, autoSize: props.autoSize }
    }

    if (props.variant === 'password') {
      return { ...base, visibilityToggle: props.visibilityToggle }
    }

    return base
  })
  </script>

  <style scoped>
  .w-full {
    width: 100%;
  }
  </style>
