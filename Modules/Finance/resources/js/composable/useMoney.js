import { usePage } from '@inertiajs/vue3'

/**
 * Amounts arrive from the API as decimal strings ("900.00") so that no
 * precision is lost in transit; every screen renders them through here to keep
 * one currency format across the module.
 */
export const useMoney = () => {
  const page = usePage()

  const locale = () => page.props.locale ?? 'en'

  const formatMoney = (value, currency = 'SAR') => {
    const amount = Number(value ?? 0)

    if (Number.isNaN(amount))
      return '—'

    return new Intl.NumberFormat(locale(), {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
    }).format(amount)
  }

  const formatDate = value => (value ? new Date(value).toLocaleDateString(locale()) : '—')

  const formatDateTime = value => (value ? new Date(value).toLocaleString(locale()) : '—')

  return { formatMoney, formatDate, formatDateTime }
}
