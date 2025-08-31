import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FileText, Download, Award, Printer } from 'lucide-react';
import type { SharesAccount, ShareCertificate } from '@/types/api';

interface SharesCertificateProps {
  account: SharesAccount | null;
  certificates: ShareCertificate[];
}

export function SharesCertificate({ account, certificates }: SharesCertificateProps) {
  const handleDownloadCertificate = () => {
    // This would generate and download a PDF certificate
    console.log('Downloading certificate...');
  };

  const handlePrintCertificate = () => {
    // This would open print dialog
    window.print();
  };

  if (!account || account.total_shares === 0) {
    return (
      <Card>
        <CardContent className="flex flex-col items-center justify-center py-12">
          <FileText className="w-12 h-12 text-muted-foreground mb-4" />
          <h3 className="text-lg font-medium mb-2">No Shares Certificate</h3>
          <p className="text-muted-foreground text-center mb-4">
            You need to own shares to generate a certificate. Purchase shares to get started.
          </p>
          <Button>Buy Your First Shares</Button>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      {/* Certificate Actions */}
      <div className="flex gap-4 justify-center">
        <Button onClick={handleDownloadCertificate} className="gap-2">
          <Download className="w-4 h-4" />
          Download Certificate
        </Button>
        <Button variant="outline" onClick={handlePrintCertificate} className="gap-2">
          <Printer className="w-4 h-4" />
          Print Certificate
        </Button>
      </div>

      {/* Certificate Preview */}
      <Card className="bg-gradient-to-br from-primary/5 to-accent/5 border-2 border-primary/20">
        <CardHeader className="text-center">
          <div className="mx-auto mb-4">
            <Award className="w-16 h-16 text-primary mx-auto mb-2" />
          </div>
          <CardTitle className="text-2xl font-heading">
            SHARE CERTIFICATE
          </CardTitle>
          <p className="text-sm text-muted-foreground">
            This certifies that the person named below is the registered holder of shares
          </p>
        </CardHeader>
        
        <CardContent className="space-y-6">
          {/* Certificate Details */}
          <div className="text-center space-y-4">
            <div className="p-6 bg-background/50 rounded-lg border">
              <h3 className="text-lg font-medium mb-4">Certificate Details</h3>
              
              <div className="grid grid-cols-2 gap-6 text-sm">
                <div>
                  <p className="text-muted-foreground">Certificate Number</p>
                  <p className="font-mono text-lg font-bold">
                    CERT-{account.id.toString().padStart(6, '0')}
                  </p>
                </div>
                
                <div>
                  <p className="text-muted-foreground">Number of Shares</p>
                  <p className="text-2xl font-bold text-primary">
                    {account.total_shares.toLocaleString()}
                  </p>
                </div>
                
                <div>
                  <p className="text-muted-foreground">Share Value</p>
                  <p className="font-bold">
                    KES {account.share_value.toLocaleString()} per share
                  </p>
                </div>
                
                <div>
                  <p className="text-muted-foreground">Total Value</p>
                  <p className="text-xl font-bold text-success">
                    KES {account.total_value.toLocaleString()}
                  </p>
                </div>
              </div>
            </div>

            {/* Share Classes */}
            <div className="p-4 bg-muted/50 rounded-lg">
              <h4 className="font-medium mb-2">Share Classification</h4>
              <Badge variant="default" className="text-sm">
                Ordinary Shares - Class A
              </Badge>
            </div>

            {/* Rights and Privileges */}
            <div className="text-left p-4 bg-background/50 rounded-lg border">
              <h4 className="font-medium mb-3">Rights and Privileges</h4>
              <ul className="text-sm text-muted-foreground space-y-2">
                <li className="flex items-start gap-2">
                  <span className="text-success font-bold">•</span>
                  Right to receive dividends as declared by the Board of Directors
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-success font-bold">•</span>
                  Voting rights in Annual General Meetings and Special General Meetings
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-success font-bold">•</span>
                  Right to participate in the distribution of surplus assets upon dissolution
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-success font-bold">•</span>
                  Right to transfer shares subject to SACCO bylaws and regulations
                </li>
              </ul>
            </div>

            {/* Certificate Footer */}
            <div className="text-center pt-6 border-t">
              <p className="text-xs text-muted-foreground">
                This certificate is issued under the authority of the SACCO Act and is valid until transferred or cancelled.
              </p>
              <p className="text-xs text-muted-foreground mt-2">
                Date of Issue: {new Date().toLocaleDateString()}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Individual Certificates */}
      {certificates.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Certificate History</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {certificates.map((cert) => (
                <div key={cert.id} className="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                  <div>
                    <p className="font-medium">{cert.certificate_number}</p>
                    <p className="text-sm text-muted-foreground">
                      {cert.shares_count} shares @ KES {cert.purchase_price.toLocaleString()}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm text-muted-foreground">
                      {new Date(cert.purchase_date).toLocaleDateString()}
                    </p>
                    <Button variant="ghost" size="sm">
                      <Download className="w-3 h-3" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Certificate Information */}
      <Card>
        <CardHeader>
          <CardTitle>About Your Certificate</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="p-4 bg-muted/50 rounded-lg">
            <h4 className="font-medium mb-2">Important Notes:</h4>
            <ul className="text-sm text-muted-foreground space-y-1">
              <li>• This certificate serves as proof of your shareholding in the SACCO</li>
              <li>• Keep this certificate safe as it may be required for certain transactions</li>
              <li>• The certificate is automatically updated when you buy or sell shares</li>
              <li>• Contact the SACCO office for any queries regarding your shares</li>
            </ul>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}