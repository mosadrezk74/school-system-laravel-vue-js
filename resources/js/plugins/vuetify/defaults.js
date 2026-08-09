/*
 * Component-level half of the design system; the other half lives in
 * resources/styles/styles.scss.
 *
 * Anything that can be expressed as a Vuetify default belongs here rather
 * than as a CSS override, so pages inherit the decision instead of having to
 * repeat a prop. In particular `flat` cards + a border from the stylesheet
 * replace the template's default drop shadows, and tables default to the
 * `compact` density the brief asks for.
 */
export default {
  IconBtn: {
    icon: true,
    color: 'default',
    variant: 'text',
  },
  VAlert: {
    density: 'comfortable',
    variant: 'tonal',
    border: 'start',
    VBtn: {
      color: undefined,
    },
  },
  VCard: {
    // The border and soft shadow come from the stylesheet; Vuetify's own
    // elevation would stack on top of them and re-create the heavy card look.
    flat: true,
  },
  VDialog: {
    // Focused content, not a full-bleed sheet.
    maxWidth: 560,
    scrollable: true,
  },
  VAvatar: {
    // ℹ️ Remove after next release
    variant: 'flat',
  },

  // VImg: {
  //   eager: true,
  // },
  VBadge: {
    // set v-badge default color to primary
    color: 'primary',
  },
  VBtn: {
    // set v-btn default color to primary
    color: 'primary',
    ripple: false,
  },
  VChip: {
    label: true,
  },
  VDataTable: {
    density: 'compact',
    hover: true,
    VPagination: {
      showFirstLastPage: true,
      firstIcon: 'bx-chevrons-left',
      lastIcon: 'bx-chevrons-right',
    },
  },
  VTable: {
    VCheckboxBtn: {
      density: 'comfortable',
    },
  },
  VDataTableServer: {
    density: 'compact',
    hover: true,
    VPagination: {
      showFirstLastPage: true,
      firstIcon: 'bx-chevrons-left',
      lastIcon: 'bx-chevrons-right',
    },
  },
  VList: {
    color: 'primary',
    density: 'compact',
    VCheckboxBtn: {
      density: 'compact',
    },
    VListItem: {
      ripple: false,
      VAvatar: {
        size: 38,
      },
    },
  },
  VMenu: {
    offset: '2px',
  },
  VPagination: {
    density: 'comfortable',
    variant: 'tonal',
  },
  VTabs: {
    // set v-tabs default color to primary
    color: 'primary',
    density: 'comfortable',
    VSlideGroup: {
      showArrows: true,
    },
  },
  VTooltip: {
    // set v-tooltip default location to top
    location: 'top',
  },
  VCheckboxBtn: {
    color: 'primary',
  },
  VCheckbox: {
    // set v-checkbox default color to primary
    color: 'primary',
    density: 'comfortable',
    hideDetails: 'auto',
  },
  VRadioGroup: {
    color: 'primary',
    density: 'comfortable',
    hideDetails: 'auto',
  },
  VRadio: {
    density: 'comfortable',
    hideDetails: 'auto',
  },
  VSelect: {
    variant: 'outlined',
    color: 'primary',
    density: 'comfortable',
    hideDetails: 'auto',
    VChip: {
      label: true,
    },
  },
  VRangeSlider: {
    // set v-range-slider default color to primary
    color: 'primary',
    trackSize: 6,
    thumbSize: 22,
    density: 'comfortable',
    thumbLabel: true,
    hideDetails: 'auto',
  },
  VRating: {
    // set v-rating default color to primary
    activeColor: 'warning',
    color: 'disabled',
    emptyIcon: 'bx-bxs-star',
  },
  VProgressLinear: {
    height: 6,
    roundedBar: true,
    rounded: true,
    bgColor: 'rgba(var(--v-track-bg))',
    color: 'primary',
  },
  VSlider: {
    // set v-range-slider default color to primary
    color: 'primary',
    thumbLabel: true,
    hideDetails: 'auto',
    thumbSize: 22,
    trackSize: 6,
    elevation: 4,
  },
  VTextField: {
    variant: 'outlined',
    density: 'comfortable',
    color: 'primary',
    hideDetails: 'auto',
  },
  VAutocomplete: {
    variant: 'outlined',
    color: 'primary',
    density: 'comfortable',
    hideDetails: 'auto',
    menuProps: {
      contentClass: 'app-autocomplete__content v-autocomplete__content',
    },
    VChip: {
      label: true,
    },
  },
  VCombobox: {
    variant: 'outlined',
    density: 'comfortable',
    color: 'primary',
    hideDetails: 'auto',
    VChip: {
      label: true,
    },
  },
  VFileInput: {
    variant: 'outlined',
    density: 'comfortable',
    color: 'primary',
    hideDetails: 'auto',
    prependIcon: '',
    prependInnerIcon: 'bx-link',
  },
  VTextarea: {
    variant: 'outlined',
    density: 'comfortable',
    color: 'primary',
    hideDetails: 'auto',
  },
  VSnackbar: {
    VBtn: {
      size: 'small',
    },
  },
  VSwitch: {
    // set v-switch default color to primary
    inset: true,
    color: 'primary',
    hideDetails: 'auto',
    ripple: false,
  },
  VNavigationDrawer: {
    touchless: true,
  },
}
