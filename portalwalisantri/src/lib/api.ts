import axios from 'axios';

const api = axios.create({
  baseURL: '/api/ct-mobile',
  withCredentials: true,
  timeout: 30000, // 30 seconds
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Add a request interceptor to handle CSRF tokens if needed
api.interceptors.request.use((config) => {
  // Laravel automatically sets XSRF-TOKEN cookie, axios handles it by default
  // if xsrfCookieName and xsrfHeaderName are set.
  return config;
});

// Add a response interceptor to handle authentication failures
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const hash = window.location.hash;
      if (hash !== '#/login') {
        window.location.href = '/ct-mobile/login#/login';
      }
    }
    return Promise.reject(error);
  }
);

export const postLogin = (data: any) => api.post('/login', data);
export const postLogout = () => api.post('/logout');
export const fetchDashboard = () => api.get('/dashboard');
export const fetchInformations = (params?: any) => api.get('/informations', { params });
export const fetchInformationDetail = (id: string | number) => api.get(`/informations/${id}`);
export const fetchStudents = () => api.get('/students');
export const fetchActiveStudent = () => api.get('/active-student');
export const fetchSaldoHistories = (params?: any) => api.get('/saldo-histories', { params });
export const fetchSavingHistories = (params?: any) => api.get('/saving-histories', { params });
export const fetchBills = () => api.get('/bills');
export const fetchBillDetail = (id: string | number) => api.get(`/bills/${id}`);
export const fetchPosTransactions = (params?: any) => api.get('/pos-transactions', { params });
export const postTopup = (data: any) => api.post('/topup', data);
export const postCheckout = (data: any) => api.post('/checkout', data);
export const fetchPaymentDetail = (id: string | number) => api.get(`/payment/${id}`);
export const uploadPaymentProof = (id: string | number, formData: FormData) => 
  api.post(`/payment/${id}/upload-proof`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
export const fetchLimit = () => api.get('/limit');
export const updateLimit = (data: any) => api.put('/limit', data);
export const fetchProfile = () => api.get('/profile');
export const postSwitchRole = () => api.post('/switch-role');
export const updateProfile = (formData: FormData) => 
  api.post('/profile', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
export const updatePassword = (data: any) => api.put('/password', data);
export const fetchPaymentMethods = (params?: { type?: string; bill_ids?: string[] }) => api.get('/payment-methods', { params });

// New API helper functions for placeholder pages
export const fetchBlockStatus = () => api.get('/block-status');
export const toggleBlock = () => api.post('/block-toggle');
export const fetchTahfidz = () => api.get('/tahfidz');
export const fetchCounseling = () => api.get('/counseling');
export const fetchAchievements = () => api.get('/achievements');
export const fetchStudyGrades = (params?: { semester_id?: string | number }) => api.get('/study-grades', { params });
export const fetchSemesters = () => api.get('/semesters');
export const fetchOfficers = () => api.get('/officers');

// --- PERIZINAN API METHODS ---
export const fetchPermits = () => api.get('/permits');
export const postPermitRequest = (data: any) => api.post('/permits', data);
export const fetchPermitDetail = (id: string | number) => api.get(`/permits/${id}`);

// --- ASATIDZ API METHODS ---
export const fetchPendingPermits = () => api.get('/asatidz/permits/pending');
export const postPermitAction = (id: string | number, data: { action: 'approve' | 'reject', rejection_reason?: string }) => 
  api.post(`/asatidz/permits/${id}/action`, data);
export const fetchActivePermits = () => api.get('/asatidz/permits/active');
export const fetchOverduePermits = () => api.get('/asatidz/permits/overdue');
export const postScanBarcode = (data: { 
  barcode_token: string, 
  latitude?: string, 
  longitude?: string, 
  photo_santri: string, 
  photo_escort: string, 
  escort_name?: string, 
  escort_relation?: string 
}) => api.post('/asatidz/permits/scan', data);
export const fetchAsatidzStats = () => api.get('/asatidz/dashboard-stats');
export const fetchMyStudents = () => api.get('/asatidz/my-students');
export const fetchStudentHistory = (studentId: string | number) => api.get(`/asatidz/my-students/${studentId}/history`);

// Return flow API methods
export const postReportReturn = (id: string | number, data: { return_photo_santri: string; return_photo_escort: string; latitude?: string; longitude?: string }) => 
  api.post(`/permits/${id}/report-return`, data);
export const fetchPendingReturnPermits = () => api.get('/asatidz/permits/pending-return');
export const postPermitReturnAction = (id: string | number, data: { action: 'approve' | 'reject'; rejection_reason?: string }) => 
  api.post(`/asatidz/permits/${id}/action-return`, data);

export default api;
