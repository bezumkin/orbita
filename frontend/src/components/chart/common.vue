<template>
  <div v-if="values.length">
    <BRow class="mb-2 row-gap-3" align-v="center">
      <BCol md="6" class="d-flex gap-3 justify-content-center justify-content-md-start order-1 order-md-0">
        <span class="fw-bold">{{ formatter(sum, 'sum') }}</span>
        <span v-if="diff > '0'" class="text-success">{{ diff }}%&nbsp;&uarr;</span>
        <span v-else-if="diff < '0'" class="text-danger">{{ diff }}%&nbsp;&darr;</span>
      </BCol>
      <BCol md="6">
        <div class="d-flex gap-3 justify-content-center justify-content-md-end order-0 order-md-1">
          <BButton :disabled="filterDisabled || nextDisabled" @click="onNext">&langle;</BButton>
          <BFormSelect v-model="filter" :disabled="filterDisabled" class="w-auto" :options="options" />
          <BButton :disabled="filterDisabled || prevDisabled" @click="onPrev">&rangle;</BButton>
        </div>
      </BCol>
    </BRow>
    <div :style="{position: 'relative', height: $isMobile ? '200px' : '300px'}">
      <canvas ref="image"></canvas>
    </div>
  </div>
</template>

<script setup lang="ts">
import {Chart, LinearScale, CategoryScale, LineController, LineElement, PointElement, Filler, Tooltip} from 'chart.js'
import Annotation from 'chartjs-plugin-annotation'
import {format} from 'date-fns'

const props = defineProps({
  endpoint: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    default: '',
  },
  event: {
    type: String,
    default: '',
  },
  formatter: {
    type: Function,
    default(value: any) {
      return value
    },
  },
  date: {
    type: Array,
    default() {
      return []
    },
  },
})

const locale = useDateLocale().value
const {$i18n, $isMobile, $socket} = useNuxtApp()

let chart: Chart<'line', string[], string>
const image = ref()
const values = ref([])
const sum = ref(0)
const previous = ref(0)
const options = [
  {value: 'week', text: $i18n.t('filter.week')},
  {value: 'month', text: $i18n.t('filter.month')},
  {value: 'quarter', text: $i18n.t('filter.quarter')},
  {value: 'year', text: $i18n.t('filter.year')},
]
const filter = ref('month')
const filterPage = ref(1)
const filterPages = ref(0)
const filterDisabled = computed(() => props.date.length > 0)
const prevDisabled = computed(() => {
  return filterPage.value <= 1
})
const nextDisabled = computed(() => {
  return filterPages.value <= filterPage.value
})
const diff = computed(() => {
  if (!sum.value || !previous.value) {
    return 0
  }
  if (sum.value > previous.value) {
    return Number((sum.value / previous.value) * 100 - 100).toFixed(2)
  }

  return Number((previous.value / sum.value) * -100 + 100).toFixed(2)
})

async function fetch() {
  try {
    const data = await useGet(props.endpoint, {date: props.date, filter: filter.value, page: filterPage.value})
    values.value = data.rows
    filterPages.value = data.pages
    sum.value = data.sum
    previous.value = data.previous
  } catch (e) {}
}

async function init() {
  await fetch()
  if (!values.value.length) {
    return
  }

  Chart.register(LinearScale, CategoryScale, LineController, LineElement, PointElement, Filler, Tooltip, Annotation)

  const color = getComputedStyle(document.body).getPropertyValue('--bs-primary-rgb')
  chart = new Chart(image.value, {
    type: 'line',
    data: {
      labels: values.value.map((i: Record<string, string>) => i.date),
      datasets: [
        {
          label: '',
          data: values.value.map((i: Record<string, string>) => i.amount),
          borderWidth: 1,
          borderColor: `rgb(${color})`,
          backgroundColor: `rgba(${color}, 0.25)`,
          pointStyle: 'circle',
          pointBackgroundColor: `rgb(${color})`,
          fill: true,
          tension: 0.2,
        },
      ],
    },
    options: {
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        axis: 'x',
      },
      plugins: {
        filler: {
          propagate: false,
        },
        tooltip: {
          yAlign: 'bottom',
          xAlign: 'center',
          displayColors: false,
          callbacks: {
            title(item) {
              try {
                const date = new Date(item[0].label)
                return [formatDateShort(date), format(date, 'EEEE', {locale})]
              } catch (e) {
                return ''
              }
            },
            label: function (item: Record<string, any>) {
              return props.formatter(item.raw, 'label')
            },
          },
        },
        annotation: {
          annotations: [],
        },
      },
      scales: {
        x: {
          ticks: {
            autoSkip: true,
            // maxRotation: 0,
            callback(_, idx) {
              const labels = values.value.map((i: Record<string, string>) => i.date)
              const date = new Date(labels[idx])
              try {
                return format(date, 'dd.MM.yy', {locale})
              } catch (e) {
                return ''
              }
            },
          },
          afterFit: (axis) => {
            axis.paddingRight = 0
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            callback(value) {
              return value ? props.formatter(value, 'tick') : ''
            },
          },
        },
      },
    },
  })
  addChartAnnotations()
}

async function update() {
  await fetch()
  if (chart) {
    chart.data.labels = values.value.map((i: Record<string, string>) => i.date)
    chart.data.datasets[0].data = values.value.map((i: Record<string, string>) => i.amount)
    addChartAnnotations()
  }
}

function addChartAnnotations() {
  const color = getComputedStyle(document.body).getPropertyValue('--bs-primary-rgb')
  const borderColor = `rgba(${color}, 0.5)`
  if (chart.options.plugins?.annotation?.annotations) {
    const annotations: any = []
    chart.data.labels?.forEach((value) => {
      const date = new Date(value as string)
      if (['year', 'quarter'].includes(filter.value)) {
        if (format(date, 'd') === '1') {
          annotations.push({type: 'line', scaleID: 'x', value, borderColor, borderWidth: 2})
        }
      } else if (format(date, 'i') === '1') {
        annotations.push({type: 'line', scaleID: 'x', value, borderColor, borderWidth: 1})
      }
    })
    chart.options.plugins.annotation.annotations = annotations
  }
  chart.update()
}

function onPrev() {
  if (filterPage.value > 1) {
    filterPage.value--
  }
}
function onNext() {
  filterPage.value++
}

function getName() {
  return props.name !== '' ? props.name : props.endpoint?.split('/').join('-')
}

function getStorage() {
  const storage = JSON.parse(localStorage.getItem('chart') || '{}')
  return storage[getName()] || {}
}

function setStorage(key: string, value: any) {
  const storage = JSON.parse(localStorage.getItem('chart') || '{}')
  const name = getName()
  if (!storage[name]) {
    storage[name] = {}
  }
  storage[name][key] = value
  localStorage.setItem('chart', JSON.stringify(storage))
}

watch([() => props.date, filterPage], update)
watch(filter, (newValue) => {
  setStorage('filter', newValue)
  if (filterPage.value !== 1) {
    filterPage.value = 1
  } else {
    update()
  }
})

onMounted(() => {
  const storage = getStorage()
  if (storage.filter) {
    filter.value = storage.filter
  }

  init()
  if (props.event) {
    $socket.on(props.event, () => {
      if (!props.date.length && filterPage.value === 1) {
        fetch()
      }
    })
  }
})

onBeforeUnmount(() => {
  if (props.event) {
    $socket.off(props.event)
  }
})
</script>
