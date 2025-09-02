import { useToast } from '@/hooks/use-toast';
import type { ApiError } from '@/types/api';

export function useApiError() {
  const { toast } = useToast();

  const handleError = (error: any, defaultMessage = 'An error occurred') => {
    let message = defaultMessage;
    let description = '';

    if (error?.response?.data) {
      const apiError = error.response.data as ApiError;
      message = apiError.message || defaultMessage;
      
      if (apiError.errors) {
        // Handle validation errors
        const validationErrors = Object.values(apiError.errors).flat();
        description = validationErrors.join(', ');
      }
    } else if (error?.message) {
      message = error.message;
    }

    toast({
      title: "Error",
      description: description || message,
      variant: "destructive",
    });
  };

  const handleSuccess = (message: string, description?: string) => {
    toast({
      title: "Success",
      description: description || message,
    });
  };

  return { handleError, handleSuccess };
}