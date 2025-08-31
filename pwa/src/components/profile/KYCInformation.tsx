import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { FileText, CheckCircle, AlertCircle, Upload } from 'lucide-react';

interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  id_number?: string;
  created_at: string;
}

interface KYCInformationProps {
  user: User | null;
}

export function KYCInformation({ user }: KYCInformationProps) {
  // Mock KYC data - in real app this would come from API
  const kycData = {
    status: 'pending', // 'completed', 'pending', 'rejected'
    documents: [
      {
        type: 'National ID',
        status: 'verified',
        uploaded_at: '2024-01-15',
      },
      {
        type: 'Proof of Address',
        status: 'pending',
        uploaded_at: null,
      },
      {
        type: 'Passport Photo',
        status: 'verified',
        uploaded_at: '2024-01-15',
      },
    ],
    verification_level: 'basic', // 'basic', 'enhanced', 'premium'
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'verified':
      case 'completed':
        return 'default';
      case 'pending':
        return 'secondary';
      case 'rejected':
        return 'destructive';
      default:
        return 'outline';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'verified':
      case 'completed':
        return <CheckCircle className="w-4 h-4 text-success" />;
      case 'pending':
        return <AlertCircle className="w-4 h-4 text-warning" />;
      case 'rejected':
        return <AlertCircle className="w-4 h-4 text-destructive" />;
      default:
        return <FileText className="w-4 h-4 text-muted-foreground" />;
    }
  };

  return (
    <div className="space-y-6">
      {/* KYC Status Overview */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <FileText className="w-5 h-5" />
            KYC Verification Status
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-medium">Overall Status</h3>
              <p className="text-sm text-muted-foreground">
                Your account verification status
              </p>
            </div>
            <Badge variant={getStatusColor(kycData.status)} className="gap-1">
              {getStatusIcon(kycData.status)}
              {kycData.status.charAt(0).toUpperCase() + kycData.status.slice(1)}
            </Badge>
          </div>

          <Separator />

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="text-center p-4 bg-muted/50 rounded-lg">
              <p className="text-sm text-muted-foreground">Verification Level</p>
              <p className="font-medium capitalize">{kycData.verification_level}</p>
            </div>
            <div className="text-center p-4 bg-muted/50 rounded-lg">
              <p className="text-sm text-muted-foreground">Documents Verified</p>
              <p className="font-medium">
                {kycData.documents.filter(doc => doc.status === 'verified').length} / {kycData.documents.length}
              </p>
            </div>
            <div className="text-center p-4 bg-muted/50 rounded-lg">
              <p className="text-sm text-muted-foreground">Member Since</p>
              <p className="font-medium">
                {user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Personal Information */}
      <Card>
        <CardHeader>
          <CardTitle>Personal Information</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label className="text-sm font-medium text-muted-foreground">Full Name</Label>
              <p className="mt-1 font-medium">{user?.name || 'Not provided'}</p>
            </div>
            <div>
              <Label className="text-sm font-medium text-muted-foreground">Email Address</Label>
              <p className="mt-1 font-medium">{user?.email}</p>
            </div>
            <div>
              <Label className="text-sm font-medium text-muted-foreground">Phone Number</Label>
              <p className="mt-1 font-medium">{user?.phone || 'Not provided'}</p>
            </div>
            <div>
              <Label className="text-sm font-medium text-muted-foreground">ID Number</Label>
              <p className="mt-1 font-medium">{user?.id_number || 'Not provided'}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Document Verification */}
      <Card>
        <CardHeader>
          <CardTitle>Document Verification</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {kycData.documents.map((document, index) => (
            <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
              <div className="flex items-center gap-3">
                {getStatusIcon(document.status)}
                <div>
                  <h4 className="font-medium">{document.type}</h4>
                  <p className="text-sm text-muted-foreground">
                    {document.uploaded_at 
                      ? `Uploaded: ${new Date(document.uploaded_at).toLocaleDateString()}`
                      : 'Not uploaded'
                    }
                  </p>
                </div>
              </div>
              
              <div className="flex items-center gap-2">
                <Badge variant={getStatusColor(document.status)}>
                  {document.status}
                </Badge>
                {document.status === 'pending' && (
                  <Button size="sm" variant="outline">
                    <Upload className="w-4 h-4 mr-2" />
                    Upload
                  </Button>
                )}
              </div>
            </div>
          ))}
        </CardContent>
      </Card>

      {/* KYC Benefits */}
      <Card>
        <CardHeader>
          <CardTitle>Verification Benefits</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            <div className="p-4 bg-success/10 rounded-lg border border-success/20">
              <h4 className="font-medium text-success mb-2">Enhanced Security</h4>
              <p className="text-sm text-muted-foreground">
                KYC verification helps protect your account and prevents unauthorized access.
              </p>
            </div>
            
            <div className="p-4 bg-primary/10 rounded-lg border border-primary/20">
              <h4 className="font-medium text-primary mb-2">Higher Transaction Limits</h4>
              <p className="text-sm text-muted-foreground">
                Verified accounts enjoy higher daily and monthly transaction limits.
              </p>
            </div>
            
            <div className="p-4 bg-accent/10 rounded-lg border border-accent/20">
              <h4 className="font-medium text-accent mb-2">Access to Premium Services</h4>
              <p className="text-sm text-muted-foreground">
                Unlock premium loan products and investment opportunities.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

function Label({ className, children, ...props }: any) {
  return (
    <label className={`text-sm font-medium text-muted-foreground ${className}`} {...props}>
      {children}
    </label>
  );
}