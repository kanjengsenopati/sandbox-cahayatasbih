import axios from 'axios';

const api = axios.create({
  baseURL: '/api/wali',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

export const postLogin = (data: any) => api.post('/login', data);
export const postLogout = () => api.post('/logout');
export const fetchDashboard = () => api.get('/dashboard');
export const fetchInformations = (params?: any) => api.get('/informations', { params });
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
export const updateProfile = (formData: FormData) => 
  api.post('/profile', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
export const updatePassword = (data: any) => api.put('/password', data);

export default api;
