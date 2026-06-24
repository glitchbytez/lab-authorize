<?php
interface ApiContract
{
    // Records
    public function getPendingRecords(): array;
    public function getCompletedRecords(): array;
    public function verifyRecord(string $accessionId, string $notes, string $scientist): array;
    public function rejectRecord(string $accessionId, string $notes, string $scientist): array;
    public function recheckRecord(string $accessionId, string $notes): array;

    // Labs
    public function getLabs(): array;
    public function createLab(string $name, ?string $ahfoz): array;
    public function deleteLab(string $name): array;

    // Users
    public function getUsers(): array;
    public function createUser(string $name, string $role, ?string $lab, string $password): array;
    public function deleteUser(string $userId): array;
}
