import { useEffect, useMemo, useState } from 'react';
import { membershipsAPI } from '@/api/memberships';
import type { Membership, User } from '@/types/api';
import { useSelector } from 'react-redux';
import { RootState } from '@/store';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Progress } from '@/components/ui/progress';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useToast } from '@/hooks/use-toast';

type MembershipWithUser = Membership & { user: User };

function getProfileTypeBadge(type: string) {
  const map: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' }> = {
    'App\\Models\\Membership\\IndividualProfile': { label: 'Individual', variant: 'default' },
    'App\\Models\\Membership\\VslaProfile': { label: 'VSLA', variant: 'secondary' },
    'App\\Models\\Membership\\MfiProfile': { label: 'MFI', variant: 'outline' },
  };
  return map[type] || { label: 'Unknown', variant: 'outline' };
}

function approvalProgress(m: MembershipWithUser) {
  const steps = [m.approved_at_level_1, m.approved_at_level_2, m.approved_at_level_3].filter(Boolean).length;
  return Math.round((steps / 3) * 100);
}

function currentWaiting(m: MembershipWithUser) {
  if (!m.approved_at_level_1) return 'Waiting Level 1';
  if (!m.approved_at_level_2) return 'Waiting Level 2';
  if (!m.approved_at_level_3) return 'Waiting Level 3';
  return 'Approved';
}

export default function MembershipRequests() {
  const { toast } = useToast();
  const user = useSelector((s: RootState) => s.auth.user);
  const [data, setData] = useState<MembershipWithUser[]>([]);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [filters, setFilters] = useState<{ profile_type?: string; approval_status?: string; search?: string }>({});
  const [selected, setSelected] = useState<MembershipWithUser | null>(null);

  const role = user?.role;

  const canApproveLevel = useMemo(() => ({
    1: role === 'staff_level_1',
    2: role === 'staff_level_2',
    3: role === 'staff_level_3',
  }), [role]);

  async function load() {
    setLoading(true);
    try {
      const res = await membershipsAPI.list({ ...filters, page });
      if (res.success) {
        const pg = res.data;
        setData(pg.data);
        setTotalPages(pg.last_page);
      }
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { load(); /* eslint-disable-next-line react-hooks/exhaustive-deps */ }, [page, JSON.stringify(filters)]);

  const onApprove = async (membershipId: number, level: 1 | 2 | 3) => {
    try {
      const res = await membershipsAPI.approveLevel(membershipId, level);
      toast({ title: 'Success', description: res.message });
      await load();
    } catch (e: any) {
      toast({ title: 'Error', description: e.message || 'Approval failed', variant: 'destructive' });
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-xl font-semibold">Membership Approval Requests</h2>
        {role && <Badge variant="outline">Role: {role}</Badge>}
      </div>

      <Card>
        <CardContent className="pt-6 grid grid-cols-1 md:grid-cols-4 gap-3">
          <div>
            <label className="text-sm text-muted-foreground">Profile Type</label>
            <Select onValueChange={(v) => setFilters(f => ({ ...f, profile_type: v || undefined }))}>
              <SelectTrigger><SelectValue placeholder="All Types" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="">All Types</SelectItem>
                <SelectItem value="individual">Individual</SelectItem>
                <SelectItem value="vsla">VSLA</SelectItem>
                <SelectItem value="mfi">MFI</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div>
            <label className="text-sm text-muted-foreground">Status</label>
            <Select onValueChange={(v) => setFilters(f => ({ ...f, approval_status: v || undefined }))}>
              <SelectTrigger><SelectValue placeholder="Pending" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="approved">Approved</SelectItem>
                <SelectItem value="rejected" disabled>Rejected</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="md:col-span-2">
            <label className="text-sm text-muted-foreground">Search</label>
            <Input placeholder="Search by name, email, or ID" onChange={(e) => setFilters(f => ({ ...f, search: e.target.value || undefined }))} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader className="flex-row items-center justify-between">
          <CardTitle>Pending Approval Requests</CardTitle>
          <Badge>{data.length} shown</Badge>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Profile Type</TableHead>
                  <TableHead>Submitted</TableHead>
                  <TableHead>Progress</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.map(m => {
                  const badge = getProfileTypeBadge(m.profile_type);
                  const progress = approvalProgress(m);
                  const waiting = currentWaiting(m);
                  return (
                    <TableRow key={m.id}>
                      <TableCell>#{m.id}</TableCell>
                      <TableCell>{m.user?.name}</TableCell>
                      <TableCell><Badge variant={badge.variant}>{badge.label}</Badge></TableCell>
                      <TableCell>{new Date(m.created_at).toLocaleDateString()}</TableCell>
                      <TableCell className="w-[200px]">
                        <div className="space-y-1">
                          <Progress value={progress} />
                          <span className="text-xs text-muted-foreground">{Math.round(progress/33)} / 3 approved</span>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant={waiting === 'Approved' ? 'default' : 'secondary'}>{waiting}</Badge>
                      </TableCell>
                      <TableCell className="space-x-2">
                        <Button variant="outline" size="sm" onClick={() => setSelected(m)}>View</Button>
                        <Button
                          size="sm"
                          disabled={
                            (waiting !== 'Waiting Level 1' || !canApproveLevel[1]) &&
                            (waiting !== 'Waiting Level 2' || !canApproveLevel[2]) &&
                            (waiting !== 'Waiting Level 3' || !canApproveLevel[3])
                          }
                          onClick={() => {
                            const level: 1 | 2 | 3 = waiting === 'Waiting Level 1' ? 1 : waiting === 'Waiting Level 2' ? 2 : 3;
                            onApprove(m.id, level);
                          }}
                        >
                          {waiting.startsWith('Waiting') ? 'Approve' : 'Completed'}
                        </Button>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </div>

          <div className="flex items-center justify-end gap-2 mt-4">
            <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage(p => Math.max(1, p - 1))}>Prev</Button>
            <span className="text-sm">Page {page} of {totalPages}</span>
            <Button variant="outline" size="sm" disabled={page >= totalPages} onClick={() => setPage(p => Math.min(totalPages, p + 1))}>Next</Button>
          </div>
        </CardContent>
      </Card>

      <Dialog open={!!selected} onOpenChange={(o) => !o && setSelected(null)}>
        <DialogContent className="max-w-3xl">
          {selected && (
            <>
              <DialogHeader>
                <DialogTitle>Membership Request #{selected.id}</DialogTitle>
              </DialogHeader>

              <Tabs defaultValue="profile">
                <TabsList>
                  <TabsTrigger value="profile">Profile</TabsTrigger>
                  <TabsTrigger value="kyc">KYC Documents</TabsTrigger>
                  <TabsTrigger value="approval">Approval</TabsTrigger>
                </TabsList>

                <TabsContent value="profile" className="space-y-3">
                  <Card>
                    <CardHeader><CardTitle>Basic Info</CardTitle></CardHeader>
                    <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div>
                        <label className="text-xs text-muted-foreground">Name</label>
                        <div>{selected.user?.name}</div>
                      </div>
                      <div>
                        <label className="text-xs text-muted-foreground">Email</label>
                        <div>{selected.user?.email}</div>
                      </div>
                      <div>
                        <label className="text-xs text-muted-foreground">Joined</label>
                        <div>{new Date(selected.created_at).toLocaleString()}</div>
                      </div>
                    </CardContent>
                  </Card>
                </TabsContent>

                <TabsContent value="kyc">
                  <Card>
                    <CardHeader><CardTitle>KYC Documents</CardTitle></CardHeader>
                    <CardContent>
                      <div className="text-sm text-muted-foreground">No document previews yet. Integrate storage when ready.</div>
                    </CardContent>
                  </Card>
                </TabsContent>

                <TabsContent value="approval" className="space-y-3">
                  <Card>
                    <CardHeader><CardTitle>Approval Progress</CardTitle></CardHeader>
                    <CardContent className="space-y-2">
                      <div className={`p-3 border rounded ${selected.approved_at_level_1 ? 'bg-green-50' : ''}`}>
                        <div className="flex items-center justify-between">
                          <span>Level 1</span>
                          <Badge variant={selected.approved_at_level_1 ? 'default' : 'secondary'}>
                            {selected.approved_at_level_1 ? 'Approved' : 'Pending'}
                          </Badge>
                        </div>
                      </div>
                      <div className={`p-3 border rounded ${selected.approved_at_level_2 ? 'bg-green-50' : ''}`}>
                        <div className="flex items-center justify-between">
                          <span>Level 2</span>
                          <Badge variant={selected.approved_at_level_2 ? 'default' : 'secondary'}>
                            {selected.approved_at_level_2 ? 'Approved' : 'Pending'}
                          </Badge>
                        </div>
                      </div>
                      <div className={`p-3 border rounded ${selected.approved_at_level_3 ? 'bg-green-50' : ''}`}>
                        <div className="flex items-center justify-between">
                          <span>Level 3</span>
                          <Badge variant={selected.approved_at_level_3 ? 'default' : 'secondary'}>
                            {selected.approved_at_level_3 ? 'Approved' : 'Pending'}
                          </Badge>
                        </div>
                      </div>
                      <div className="pt-2">
                        <Progress value={approvalProgress(selected)} />
                      </div>
                      <div className="flex justify-end">
                        <Button
                          disabled={
                            (currentWaiting(selected) === 'Waiting Level 1' && !canApproveLevel[1]) ||
                            (currentWaiting(selected) === 'Waiting Level 2' && !canApproveLevel[2]) ||
                            (currentWaiting(selected) === 'Waiting Level 3' && !canApproveLevel[3]) ||
                            currentWaiting(selected) === 'Approved'
                          }
                          onClick={() => {
                            const w = currentWaiting(selected);
                            const level: 1 | 2 | 3 = w === 'Waiting Level 1' ? 1 : w === 'Waiting Level 2' ? 2 : 3;
                            onApprove(selected.id, level);
                          }}
                        >
                          {currentWaiting(selected).startsWith('Waiting') ? 'Approve' : 'Completed'}
                        </Button>
                      </div>
                    </CardContent>
                  </Card>
                </TabsContent>
              </Tabs>
            </>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
}

