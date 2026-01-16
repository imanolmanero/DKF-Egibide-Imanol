export interface Seguimiento {
  id_seguimiento: number;
  accion: string;
  fecha: string;
  descripcion?: string | null;
  via?: string | null;
  id_estancia: number;
  created_at?: string;
  updated_at?: string;
}
