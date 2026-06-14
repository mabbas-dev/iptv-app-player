import AsyncStorage from '@react-native-async-storage/async-storage';

export type LanguageCode = 'en' | 'ar' | 'es' | 'fr' | 'ur';

const KEY = 'fox:language';

const STRINGS: Record<LanguageCode, Record<string, string>> = {
  en: {
    liveTv: 'Live TV',
    movies: 'Movies',
    series: 'Series',
    favourites: 'Favourites',
    refresh: 'Refresh',
    account: 'Account',
    settings: 'Settings',
    back: 'Back',
    home: 'Home',
    loading: 'Loading Your Content',
    pleaseWait: 'Please Wait ...',
    disclaimer: 'FoxPlayer is a media player only, and does not include any content.',
    yourMac: 'Your MAC:',
    active: 'Active',
    expiresOn: 'Expires on',
    total: 'Total',
    search: 'Search',
    noContent: 'No content found.',
    loadingStream: 'Loading stream...',
    play: 'Play',
    resume: 'Resume',
    download: 'Download',
    playlists: 'Playlists',
  },
  ar: {
    liveTv: 'بث مباشر',
    movies: 'أفلام',
    series: 'مسلسلات',
    favourites: 'المفضلة',
    refresh: 'تحديث',
    account: 'الحساب',
    settings: 'الإعدادات',
    back: 'رجوع',
    home: 'الرئيسية',
    loading: 'جاري تحميل المحتوى',
    pleaseWait: 'يرجى الانتظار ...',
    disclaimer: 'FoxPlayer مشغل وسائط فقط ولا يتضمن أي محتوى.',
    yourMac: 'عنوان MAC:',
    active: 'نشط',
    expiresOn: 'ينتهي في',
    total: 'الإجمالي',
    search: 'بحث',
    noContent: 'لا يوجد محتوى.',
    loadingStream: 'جاري تحميل البث...',
    play: 'تشغيل',
    resume: 'متابعة',
    download: 'تحميل',
    playlists: 'قوائم التشغيل',
  },
  es: {
    liveTv: 'TV en vivo',
    movies: 'Películas',
    series: 'Series',
    favourites: 'Favoritos',
    refresh: 'Actualizar',
    account: 'Cuenta',
    settings: 'Ajustes',
    back: 'Atrás',
    home: 'Inicio',
    loading: 'Cargando contenido',
    pleaseWait: 'Por favor espere ...',
    disclaimer: 'FoxPlayer es solo un reproductor y no incluye contenido.',
    yourMac: 'Tu MAC:',
    active: 'Activo',
    expiresOn: 'Expira el',
    total: 'Total',
    search: 'Buscar',
    noContent: 'Sin contenido.',
    loadingStream: 'Cargando stream...',
    play: 'Reproducir',
    resume: 'Continuar',
    download: 'Descargar',
    playlists: 'Listas',
  },
  fr: {
    liveTv: 'TV en direct',
    movies: 'Films',
    series: 'Séries',
    favourites: 'Favoris',
    refresh: 'Actualiser',
    account: 'Compte',
    settings: 'Paramètres',
    back: 'Retour',
    home: 'Accueil',
    loading: 'Chargement du contenu',
    pleaseWait: 'Veuillez patienter ...',
    disclaimer: 'FoxPlayer est un lecteur média uniquement.',
    yourMac: 'Votre MAC:',
    active: 'Actif',
    expiresOn: 'Expire le',
    total: 'Total',
    search: 'Rechercher',
    noContent: 'Aucun contenu.',
    loadingStream: 'Chargement...',
    play: 'Lire',
    resume: 'Reprendre',
    download: 'Télécharger',
    playlists: 'Playlists',
  },
  ur: {
    liveTv: 'لائیو ٹی وی',
    movies: 'موویز',
    series: 'سیریز',
    favourites: 'پسندیدہ',
    refresh: 'ریفریش',
    account: 'اکاؤنٹ',
    settings: 'سیٹنگز',
    back: 'واپس',
    home: 'ہوم',
    loading: 'مواد لوڈ ہو رہا ہے',
    pleaseWait: 'براہ کرم انتظار کریں ...',
    disclaimer: 'FoxPlayer صرف میڈیا پلیئر ہے۔',
    yourMac: 'آپ کا MAC:',
    active: 'فعال',
    expiresOn: 'ختم ہونے کی تاریخ',
    total: 'کل',
    search: 'تلاش',
    noContent: 'کوئی مواد نہیں۔',
    loadingStream: 'اسٹریم لوڈ ہو رہی ہے...',
    play: 'چلائیں',
    resume: 'جاری رکھیں',
    download: 'ڈاؤن لوڈ',
    playlists: 'پلے لسٹ',
  },
};

let currentLang: LanguageCode = 'en';

export async function initLanguage(defaultCode?: string): Promise<LanguageCode> {
  const saved = await AsyncStorage.getItem(KEY);
  if (saved && saved in STRINGS) {
    currentLang = saved as LanguageCode;
    return currentLang;
  }
  if (defaultCode && defaultCode in STRINGS) {
    currentLang = defaultCode as LanguageCode;
  }
  return currentLang;
}

export async function setLanguage(code: LanguageCode): Promise<void> {
  currentLang = code;
  await AsyncStorage.setItem(KEY, code);
}

export function getLanguage(): LanguageCode {
  return currentLang;
}

export function t(key: string): string {
  return STRINGS[currentLang][key] ?? STRINGS.en[key] ?? key;
}

export const LANGUAGES: { code: LanguageCode; label: string }[] = [
  { code: 'en', label: 'English' },
  { code: 'ar', label: 'العربية' },
  { code: 'es', label: 'Español' },
  { code: 'fr', label: 'Français' },
  { code: 'ur', label: 'اردو' },
];
