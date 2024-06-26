import Parent from './file'

export default class extends Parent {
  type: string = 'image'

  static get toolbox() {
    return {
      title: 'Image',
      icon: '<svg class="svg-inline--fa fa-fw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M0 96C0 60.7 28.7 32 64 32H448c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zM323.8 202.5c-4.5-6.6-11.9-10.5-19.8-10.5s-15.4 3.9-19.8 10.5l-87 127.6L170.7 297c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h96 32H424c8.9 0 17.1-4.9 21.2-12.8s3.6-17.4-1.4-24.7l-120-176zM112 192a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"></path></svg>',
    }
  }

  static get pasteConfig() {
    return {
      files: {
        mimeTypes: ['image/*'],
      },
    }
  }

  showPreview() {
    if (!this.data.uuid) {
      return
    }
    if (!this.data.crop || Array.isArray(this.data.crop)) {
      this.data.crop = {}
    }

    const width = Number(this.data.crop.width || 0)
    const height = Number(this.data.crop.height || 0)

    const wrapper = document.createElement('div')
    wrapper.classList.add('border', 'rounded', 'p-2')

    const image = document.createElement('img')
    image.classList.add('image', 'img-fluid', 'rounded', 'd-block', 'm-auto')
    image.src = this.getImageLink()
    if (width > 0) {
      image.width = width
    }
    if (height > 0) {
      image.height = height
    }
    wrapper.appendChild(image)

    const row = document.createElement('div')
    row.classList.add('row')

    const colWidth = document.createElement('div')
    colWidth.classList.add('col-md-4', 'col-6')
    colWidth.appendChild(this.getInputGroup('Width', String(width)))

    const colHeight = document.createElement('div')
    colHeight.classList.add('col-md-4', 'col-6')
    colHeight.appendChild(this.getInputGroup('Height', String(height)))

    const colCrop = document.createElement('div')
    colCrop.classList.add('col-md-4')
    colCrop.appendChild(this.getCropSelect())

    row.appendChild(colWidth)
    row.appendChild(colHeight)
    row.appendChild(colCrop)
    wrapper.appendChild(row)

    this.html.appendChild(wrapper)
  }

  getImageLink() {
    const options: Record<string, string> = {}
    if (this.data.crop.width) {
      options.w = this.data.crop.width
    }
    if (this.data.crop.height) {
      options.h = this.data.crop.height
    }
    options.fit = this.data.crop.fit || 'contain'

    return getImageLink(this.data as VespFile, options)
  }

  getInputGroup(name: string, value: string) {
    const group = document.createElement('fieldset')
    group.classList.add('m-md-0', 'small')

    const label = document.createElement('legend')
    label.classList.add('form-label', 'col-form-label', 'pb-0')
    label.textContent = this.api.i18n.t(name)

    const input = document.createElement('input')
    input.type = 'number'
    input.min = '0'
    input.classList.add('form-control', 'form-control-sm')
    input.value = value
    input.addEventListener('input', (e) => {
      const target = e.target as HTMLInputElement
      if (!this.data.crop) {
        this.data.crop = {}
      }
      const key = name === 'Width' ? 'width' : 'height'
      this.data.crop[key] = Number(target?.value)

      const image = this.html.querySelector('.image') as HTMLImageElement
      if (image) {
        image.src = this.getImageLink()
        if (Number(target?.value) > 0) {
          image[key] = Number(target?.value)
        }
      }
    })

    group.appendChild(label)
    group.appendChild(input)

    return group
  }

  getCropSelect() {
    const variants = [
      'contain',
      'max',
      'fill',
      'fill-max',
      'stretch',
      'crop',
      'crop-top-left',
      'crop-top',
      'crop-top-right',
      'crop-left',
      'crop-right',
      'crop-bottom-left',
      'crop-bottom',
      'crop-bottom-right',
    ]

    const group = document.createElement('fieldset')
    group.classList.add('m-md-0', 'small')

    const label = document.createElement('legend')
    label.classList.add('form-label', 'col-form-label', 'pb-0')
    label.textContent = this.api.i18n.t('Crop')

    const select = document.createElement('select')
    select.classList.add('form-select', 'form-select-sm')
    variants.forEach((val) => {
      const option = document.createElement('option')
      option.value = val
      option.textContent = val
      if (val === this.data.crop.fit) {
        option.selected = true
      }
      select.appendChild(option)
    })
    select.addEventListener('change', (e) => {
      const target = e.target as HTMLSelectElement
      if (!this.data.crop) {
        this.data.crop = {}
      }
      this.data.crop.fit = target?.value

      const image = this.html.querySelector('.image') as HTMLImageElement
      if (image) {
        image.src = this.getImageLink()
      }
    })

    group.appendChild(label)
    group.appendChild(select)

    return group
  }
}
